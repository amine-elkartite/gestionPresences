<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Absence;
use App\Models\Planning;
use App\Services\AttendanceStatusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(
        Request $request,
        AttendanceStatusService $statusService
    ): View {
        $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);
        $search = trim($request->string('search')->toString());
        $date = Carbon::parse(
            $request->input('date', today()->toDateString())
        )->toDateString();

        // employees
        $employees = Employee::orderBy('name')->get();

        $userIds = $employees->pluck('user_id')->filter();
        $employeeIds = $employees->pluck('id');

        // attendances
        $attendances = Attendance::whereDate('date', $date)
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        // absences (congé / repos / etc)
        $absences = Absence::whereDate('date', $date)
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        // planning
        $plannings = Planning::whereDate('date', $date)
            ->whereIn('employee_id', $employeeIds)
            ->get()
            ->groupBy('employee_id');

        // rows
        $attendanceRows = $employees->map(function ($employee) use (
            $attendances,
            $absences,
            $plannings,
            $statusService,
            $date
        ) {
            $attendance = $attendances->get($employee->user_id);
            $absence = $absences->get($employee->user_id);
            $planning = $plannings->get($employee->id, collect());

            // ✅ SAME LOGIC AS DASHBOARD
            $status = $statusService->finalStatus(
                $attendance,
                $planning->pluck('role'),
                $absence
            );

            // worked hours
            $minutes = 0;

            if ($attendance?->check_in_time && $attendance?->check_out_time) {
                $start = Carbon::parse($date . ' ' . $attendance->check_in_time);
                $end = Carbon::parse($date . ' ' . $attendance->check_out_time);

                if ($end->lessThan($start)) {
                    $end->addDay();
                }

                $minutes = $start->diffInMinutes($end);
            }

            return (object) [
                'employee' => $employee,
                'status' => $status,
                'check_in_time' => $attendance?->check_in_time,
                'check_out_time' => $attendance?->check_out_time,
                'worked_hours' => round($minutes / 60, 2),
                'absence_reason' => $absence?->reason ?? null,
            ];
        });

        $attendanceRows = $attendanceRows->filter(fn ($row) => $search === '' || mb_stripos($row->employee->name, $search) !== false);

        return view('attendance.index', compact(
            'attendanceRows',
            'employees',
            'date',
            'search'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date'],
            'check_in_time' => ['nullable'],
            'check_out_time' => ['nullable'],
        ]);

        Attendance::updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'date' => $data['date'],
            ],
            [
                'check_in_time' => $data['check_in_time'],
                'check_out_time' => $data['check_out_time'],
            ]
        );

        return back()->with('success', 'Saved');
    }
}
