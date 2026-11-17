<?php

namespace Tests\Feature;

use App\Models\Absence;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Planning;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRedesignTest extends TestCase
{
    use RefreshDatabase;

    private function employee(string $name, string $department): Employee
    {
        $user = User::create(['name' => $name, 'pin_code' => uniqid(), 'role' => 'employee']);

        return Employee::create(['user_id' => $user->id, 'name' => $name, 'department' => $department, 'status' => 'active']);
    }

    public function test_filters_select_daily_rows_without_changing_team_totals(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 2)->startOfDay());
        $employee = $this->employee('Alice', 'Accueil');
        $this->employee('Samir', 'Operations');
        Planning::create(['employee_id' => $employee->id, 'date' => '2026-10-01', 'period' => 'MATIN', 'role' => 'CAISSE']);
        Attendance::create(['user_id' => $employee->user_id, 'date' => '2026-10-01', 'check_in_time' => '08:00', 'check_out_time' => '16:30']);

        $this->withSession(['role' => 'admin'])
            ->get(route('admin.dashboard', ['date' => '2026-10-01', 'department' => 'Accueil', 'status' => 'present', 'search' => 'alice']))
            ->assertOk()
            ->assertViewHas('totalEmployees', 2)
            ->assertViewHas('presentToday', 1)
            ->assertViewHas('dailyRows', fn ($rows) => $rows->total() === 1 && $rows->first()->employee->id === $employee->id)
            ->assertSee('8h 30m');
    }

    public function test_weekly_statistics_use_the_existing_status_rules_and_real_events(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 2)->setTime(12, 0));
        $employee = $this->employee('Alice', 'Accueil');
        Planning::create(['employee_id' => $employee->id, 'date' => '2026-10-01', 'period' => 'MATIN', 'role' => 'CAISSE']);
        Attendance::create(['user_id' => $employee->user_id, 'date' => '2026-10-01', 'check_in_time' => '08:00']);
        Absence::create(['user_id' => $employee->user_id, 'date' => '2026-10-02', 'reason' => 'Maladie']);

        $this->withSession(['role' => 'admin'])->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('weeklyStats', fn ($days) => $days[3]['present'] === 1 && $days[4]['absent'] === 1 && $days[5]['present'] === null)
            ->assertViewHas('recentActivity', fn ($events) => $events->count() === 1 && $events->first()['name'] === 'Alice')
            ->assertSee('Maladie');
    }

    public function test_export_respects_filters_and_escapes_spreadsheet_formulas(): void
    {
        $this->employee('=FORMULA', 'Accueil');
        $this->employee('Excluded', 'Operations');
        $response = $this->withSession(['role' => 'admin'])->get(route('admin.dashboard', ['department' => 'Accueil', 'export' => 'csv']));
        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();
        $this->assertStringContainsString("'=FORMULA", $csv);
        $this->assertStringContainsString('Non planifié', $csv);
        $this->assertStringNotContainsString('Excluded', $csv);
    }

    public function test_dashboard_rejects_invalid_filters(): void
    {
        $this->withSession(['role' => 'admin'])->getJson(route('admin.dashboard', ['date' => 'broken', 'status' => 'unknown']))
            ->assertUnprocessable()->assertJsonValidationErrors(['date', 'status']);
    }

    public function test_unplanned_employees_have_a_french_label_and_empty_analytics(): void
    {
        $this->employee('Alice', 'Accueil');
        $this->withSession(['role' => 'admin'])->get(route('admin.dashboard'))
            ->assertOk()->assertSee('Non planifié')->assertSee('Aucune activité enregistrée')
            ->assertViewHas('presenceRate', null);
    }

    public function test_late_arrivals_and_overnight_hours_are_preserved(): void
    {
        $employee = $this->employee('Alice', 'Accueil');
        Planning::create(['employee_id' => $employee->id, 'date' => today()->toDateString(), 'period' => 'SOIR', 'role' => 'CAISSE']);
        Attendance::create(['user_id' => $employee->user_id, 'date' => today()->toDateString(), 'check_in_time' => '22:00', 'check_out_time' => '06:30', 'status' => 'late']);

        $this->withSession(['role' => 'admin'])->get(route('admin.dashboard', ['status' => 'late']))
            ->assertOk()->assertSee('En retard')->assertSee('8h 30m')
            ->assertViewHas('presentToday', 1)->assertViewHas('presenceRate', 100)
            ->assertViewHas('dailyRows', fn ($rows) => $rows->total() === 1);
    }

    public function test_existing_absence_and_attendance_forms_still_save_and_report_success(): void
    {
        $employee = $this->employee('Alice', 'Accueil');
        $data = ['user_id' => $employee->user_id, 'date' => today()->toDateString()];
        $this->withSession(['role' => 'admin'])->from(route('admin.dashboard'))
            ->post(route('attendance.store'), $data + ['check_in_time' => '08:00', 'check_out_time' => '16:00'])
            ->assertRedirect(route('admin.dashboard'))->assertSessionHas('success');
        $this->assertDatabaseHas('attendances', $data + ['check_in_time' => '08:00']);

        $this->from(route('admin.dashboard'))->post(route('absences.store'), $data + ['reason' => 'Maladie', '_form' => 'absence'])
            ->assertRedirect(route('admin.dashboard'))->assertSessionHas('success');
        $this->assertSame('Maladie', Absence::where('user_id', $employee->user_id)->whereDate('date', $data['date'])->firstOrFail()->reason);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Absence enregistrée avec succès.');
        $this->delete(route('absences.destroy', Absence::first()))->assertRedirect();
        $this->assertDatabaseCount('absences', 0);
    }

    public function test_absence_validation_preserves_input_and_reopens_the_dialog(): void
    {
        $employee = $this->employee('Alice', 'Accueil');
        $this->withSession(['role' => 'admin'])->from(route('admin.dashboard'))
            ->post(route('absences.store'), ['user_id' => $employee->user_id, 'date' => today()->toDateString(), 'reason' => '', '_form' => 'absence'])
            ->assertSessionHasErrors('reason')->assertRedirect(route('admin.dashboard'));
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('data-reopen', false);
        $this->assertDatabaseCount('absences', 0);
    }

    public function test_admin_identity_is_available_after_login_and_export_is_protected(): void
    {
        $this->get(route('admin.dashboard', ['export' => 'csv']))->assertRedirect(route('pin.show'));
        $user = User::create(['name' => 'Responsable RH', 'pin_code' => '9876', 'role' => 'admin']);
        $this->post(route('pin.store'), ['pin_code' => '9876'])->assertRedirect(route('admin.dashboard'))->assertSessionHas('user_id', $user->id);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Responsable RH');
        $this->post(route('logout'))->assertRedirect(route('pin.show'))->assertSessionMissing('user_id');
    }

    public function test_all_administration_pages_render_with_existing_employee_records(): void
    {
        $employee = $this->employee('Alice', 'Accueil');
        $this->withSession(['role' => 'admin']);
        foreach (['employees.index', 'employees.create', 'attendance.index', 'absences.index', 'planning.index', 'reports.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->get(route('employees.edit', $employee))->assertOk();
    }

    public function test_weekly_analytics_include_records_on_sunday(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 4)->setTime(12, 0));
        $employee = $this->employee('Alice', 'Accueil');
        Absence::create(['user_id' => $employee->user_id, 'date' => '2026-10-04', 'reason' => 'Maladie']);

        $this->withSession(['role' => 'admin'])->get(route('admin.dashboard'))
            ->assertOk()->assertViewHas('weeklyStats', fn ($days) => $days[6]['absent'] === 1);
    }
}
