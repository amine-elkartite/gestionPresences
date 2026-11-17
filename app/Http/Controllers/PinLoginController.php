<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use App\Services\AttendanceRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PinLoginController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('role')) {
            return $request->session()->get('role') === 'admin'
                ? redirect()->route('admin.dashboard')
                : redirect()->route('employee.attendance');
        }

        return view('pin-login');
    }

    public function store(Request $request, AttendanceRecorder $recorder): RedirectResponse
    {
        $validated = $request->validate([
            'pin_code' => ['required', 'string'],
            'action' => ['nullable', Rule::in(['check_in', 'check_out'])],
        ]);

        $user = User::with('employee')->where('pin_code', $validated['pin_code'])->first();

        if (! $user) {
            return back()->withInput()->with('error', 'Invalid PIN code.');
        }

        if ($user->isEmployee() && $user->employee?->status !== 'active') {
            return back()->with('error', 'This employee account is inactive.');
        }

        $request->session()->put('role', $user->role);
        $request->session()->put('user_id', $user->id);

        if ($user->isEmployee()) {
            $request->session()->put('employee_id', $user->employee->id);
        } else {
            $request->session()->forget('employee_id');
        }

        $request->session()->regenerate();
        $request->session()->regenerateToken();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $action = $validated['action'] ?? 'check_in';

        if ($action === 'check_out') {
            $recorder->checkOut($user);

            return redirect()
                ->route('employee.attendance')
                ->with('success', 'Check-out recorded successfully.');
        }

        $recorder->checkIn($user);

        return redirect()
            ->route('employee.attendance')
            ->with('success', 'Check-in recorded successfully.');
    }

    public function employeeAttendance(Request $request): View|RedirectResponse
    {
        $employeeId = $request->session()->get('employee_id');
        $employee = Employee::with('user')->find($employeeId);

        if (! $employee || ! $employee->user) {
            return redirect()->route('pin.show');
        }

        $attendance = Attendance::where('user_id', $employee->user->id)
            ->whereDate('date', today())
            ->first();

        return view('employee.attendance', [
            'attendance' => $attendance,
            'user' => $employee->user,
        ]);
    }

    public function checkIn(Request $request, AttendanceRecorder $recorder): RedirectResponse
    {
        $employeeId = $request->session()->get('employee_id');
        $employee = Employee::with('user')->find($employeeId);

        if (! $employee || ! $employee->user) {
            return redirect()->route('pin.show');
        }

        $recorder->checkIn($employee->user);

        return back()->with('success', 'Check-in recorded successfully.');
    }

    public function checkOut(Request $request, AttendanceRecorder $recorder): RedirectResponse
    {
        $employeeId = $request->session()->get('employee_id');
        $employee = Employee::with('user')->find($employeeId);

        if (! $employee || ! $employee->user) {
            return redirect()->route('pin.show');
        }

        $recorder->checkOut($employee->user);

        return back()->with('success', 'Check-out recorded successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pin.show');
    }
}
