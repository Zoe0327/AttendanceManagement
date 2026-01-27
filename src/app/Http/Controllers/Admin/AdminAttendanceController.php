<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UpdateAttendanceRequest;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        DB::transaction(function () use ($request, $attendance) {
            //勤怠本体更新
            $attendance->update([
                'start_time' => $request->work_start,
                'end_time' =>$request->work_end,
                'status' => 1, //確定
                'remark' => $request->remark,
            ]);
            
            $attendance->breaks()->delete();

            //休憩を一旦削除
            if ($request->has('breaks')) {
                foreach ($request->breaks as $break) {
                    if (!empty($break['start']) && !empty($break['end'])) {
                        $attendance->breaks()->create([
                            'start_time' => $break['start'],
                            'end_time'   => $break['end'],
                        ]);
                    }
                }
            }
        });
        return redirect()
            ->route('admin.attendance.show', $attendance->id)
            ->with('message', '勤怠を修正しました');
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
