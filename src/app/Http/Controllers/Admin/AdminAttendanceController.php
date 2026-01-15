<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $attendances = Attendance::with(['user', 'breaks'])
            ->whereDate('work_date', $date)
            ->orderBy('start_time')
            ->get();

        return view('admin.attendance.index', [
        'date' => $date,
        'attendances' => $attendances,
        ]);
    }

    public function show(Attendance $attendance)
    {
        $attendance->load(['user', 'breaks', 'correctionRequests.breakCorrections']);

        $latestCorrection = $attendance->correctionRequests()
            ->latest()
            ->first();

        $isPending = $latestCorrection && $latestCorrection->status === '0';

        return view('admin.attendance.show', [
            'attendance' => $attendance,
            'breaks' => $attendance->breaks,
            'latestCorrection' => $latestCorrection,
            'isPending' => $isPending,
        ]);
    }

    public function update(Request $request, Attendance $attendance)
    {
        return redirect()
            ->route('admin.attendance.show', $attendance->id);
    }

    public function staff(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $month = $request->query('month')
            ? Carbon::parse($request->query('month'))
            : Carbon::now();

        $attendances = $user->attendances()
            ->whereYear('work_date', $month->year)
            ->whereMonth('work_date', $month->month)
            ->orderBy('work_date')
            ->get();

        return view('admin.staff.index', [
            'user' => $user,
            'attendances' => $attendances,
            'month' => $month,
        ]);
    }
}
