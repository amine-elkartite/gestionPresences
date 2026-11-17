<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Planning;
use App\Services\AttendanceStatusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDashboardController extends Controller
{
    public const STATUS_LABELS = [
        'present' => 'Présent', 'absent' => 'Absent', 'late' => 'En retard',
        'conge' => 'Congé', 'repos' => 'Repos', 'not_planned' => 'Non planifié',
    ];

    public function index(Request $request, AttendanceStatusService $statusService): View|StreamedResponse
    {
        $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'search' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(array_keys(self::STATUS_LABELS))],
            'export' => ['nullable', Rule::in(['csv'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $today = $request->input('date') ?: today()->toDateString();
        $search = trim($request->string('search')->toString());
        $department = $request->string('department')->toString();
        $status = $request->string('status')->toString();
        $employees = Employee::with('user')->orderBy('name')->get();
        $departments = $employees->pluck('department')->filter()->unique()->sort()->values();
        $allRows = $statusService->buildDailyRows($employees, $today);

        // A recorded late arrival stays a presence in the team's attendance totals.
        $allRows->each(function ($row) {
            $row->display_status = $row->status === 'present' && $row->attendance?->status === 'late'
                ? 'late' : $row->status;
        });
        $totalEmployees = $employees->count();
        $presentToday = $allRows->where('status', 'present')->count();
        $absentToday = $allRows->where('status', 'absent')->count();
        $congeToday = $allRows->where('status', 'conge')->count();
        $reposToday = $allRows->where('status', 'repos')->count();
        $notPlanned = $allRows->where('status', 'not_planned')->count();
        $expectedEmployees = $presentToday + $absentToday;
        $presenceRate = $expectedEmployees ? (int) round($presentToday / $expectedEmployees * 100) : null;

        $filteredRows = $allRows->filter(function ($row) use ($search, $department, $status) {
            $haystack = implode(' ', [$row->employee->name, $row->employee->department, $row->employee->position]);

            return ($search === '' || mb_stripos($haystack, $search) !== false)
                && ($department === '' || $row->employee->department === $department)
                && ($status === '' || $row->display_status === $status);
        })->values();

        if ($request->input('export') === 'csv') {
            return $this->export($filteredRows, $today);
        }

        $dailyRows = new LengthAwarePaginator(
            $filteredRows->forPage($request->integer('page', 1), 10)->values(),
            $filteredRows->count(), 10, $request->integer('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $absenceMotifs = Absence::whereDate('date', $today)
            ->selectRaw('reason, count(*) as total')->groupBy('reason')->orderByDesc('total')->get();
        $weeklyStats = $this->weeklyStats($employees, $today, $statusService);
        $recentActivity = $this->recentActivity($today);

        return view('admin.dashboard', compact(
            'dailyRows', 'employees', 'departments', 'department', 'status', 'search', 'today',
            'totalEmployees', 'presentToday', 'absentToday', 'congeToday', 'reposToday',
            'notPlanned', 'expectedEmployees', 'presenceRate', 'absenceMotifs', 'weeklyStats', 'recentActivity'
        ));
    }

    private function weeklyStats(Collection $employees, string $date, AttendanceStatusService $service): array
    {
        $start = Carbon::parse($date)->startOfWeek();
        $range = [$start->toDateString(), $start->copy()->endOfWeek()->toDateTimeString()];
        $key = fn ($record) => $record->user_id . '|' . Carbon::parse($record->date)->toDateString();
        $attendances = Attendance::whereBetween('date', $range)->whereIn('user_id', $employees->pluck('user_id'))->get()->keyBy($key);
        $absences = Absence::whereBetween('date', $range)->whereIn('user_id', $employees->pluck('user_id'))->get()->keyBy($key);
        $planning = Planning::whereBetween('date', $range)->whereIn('employee_id', $employees->pluck('id'))->get()
            ->groupBy(fn ($record) => $record->employee_id . '|' . Carbon::parse($record->date)->toDateString());
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $start->copy()->addDays($i);
            $dayKey = $day->toDateString();
            $counts = ['present' => 0, 'absent' => 0];
            foreach ($employees as $employee) {
                $userKey = $employee->user_id . '|' . $dayKey;
                $final = $service->finalStatus($attendances->get($userKey), $planning->get($employee->id . '|' . $dayKey, collect())->pluck('role'), $absences->get($userKey));
                if (array_key_exists($final, $counts)) {
                    $counts[$final]++;
                }
            }
            $days[] = [
                'date' => $dayKey,
                'label' => $day->locale('fr')->translatedFormat('D'),
                'present' => $day->isAfter(today()) ? null : $counts['present'],
                'absent' => $day->isAfter(today()) ? null : $counts['absent'],
            ];
        }

        return $days;
    }

    private function recentActivity(string $date): Collection
    {
        $events = collect();
        foreach (Attendance::with('user.employee')->whereDate('date', $date)->get() as $attendance) {
            foreach (['check_in_time' => 'Entrée enregistrée', 'check_out_time' => 'Sortie enregistrée'] as $field => $label) {
                if ($attendance->$field) {
                    $time = Carbon::parse($date . ' ' . $attendance->$field);
                    if ($field === 'check_out_time' && $attendance->check_in_time && $attendance->check_out_time < $attendance->check_in_time) {
                        $time->addDay();
                    }
                    $events->push(['name' => $attendance->user?->employee?->name ?? $attendance->user?->name ?? 'Employé', 'label' => $label, 'time' => $time, 'tone' => 'present']);
                }
            }
        }
        foreach (Absence::with('user.employee')->whereDate('date', $date)->get() as $absence) {
            $events->push(['name' => $absence->user?->employee?->name ?? $absence->user?->name ?? 'Employé', 'label' => 'Absence enregistrée · ' . $absence->reason, 'time' => $absence->updated_at, 'tone' => 'absent']);
        }

        return $events->sortByDesc('time')->take(5)->values();
    }

    private function export(Collection $rows, string $date): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['Employé', 'Département', 'Statut', 'Entrée', 'Sortie', 'Minutes travaillées', 'Motif'], ';', '"', '');
            foreach ($rows as $row) {
                $values = [$row->employee->name, $row->employee->department, self::STATUS_LABELS[$row->display_status], $row->check_in_time, $row->check_out_time, $row->worked_minutes, $row->absence_reason];
                // Spreadsheet applications can execute formula-prefixed user input.
                $values = array_map(fn ($value) => preg_match('/^[\\s]*[=+@-]/u', (string) $value) ? "'" . $value : $value, $values);
                fputcsv($stream, $values, ';', '"', '');
            }
            fclose($stream);
        }, "presences-{$date}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
