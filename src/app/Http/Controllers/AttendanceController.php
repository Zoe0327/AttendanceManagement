<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\CorrectionRequest;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();

        // 今日の最新勤怠（1件だけ）
        $attendance = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->where('work_date', today())
            ->first();

        if (!$attendance) {
            $status = 'off_duty';
        } elseif (is_null($attendance->end_time)) {
            if ($attendance->breaks()->whereNull('end_time')->exists()) {
                $status = 'on_break';
            } else {
                $status = 'working';
            }
        } else {
            $status = 'finished';
        }

        return view('attendance.index', [
            'status' => $status,
            'attendance' => $attendance,
            'today' => $today->format('Y年m月d日（D）'),
            'time' => now()->format('H:i'),
        ]);
    }



    public function start()
    {
        $workingAttendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', today())
            ->whereNull('end_time')
            ->first();

        if ($workingAttendance) {
            return redirect()->route('user.attendance.index');
        }

        Attendance::create([
            'user_id'    => auth()->id(),
            'work_date' => today(),
            'start_time' => now(),
            'status'     => 'working',
        ]);

        return redirect()->route('user.attendance.index');
    }


    public function startBreak()
    {
        $attendance = Attendance::where('user_id', auth()->id())
        ->where('work_date', today())
        ->whereNull('end_time')
        ->firstOrFail();
        $attendance->breaks()->create([
            'start_time' => now(),
        ]);

        return redirect()->route('user.attendance.index');
    }

    public function endBreak()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', today())
            ->whereNull('end_time')
            ->firstOrFail();

        $break = $attendance->breaks()->whereNull('end_time')->firstOrFail();
        $break->update(['end_time' => now()]);

        return redirect()->route('user.attendance.index');
    }

    public function end()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', today())
            ->whereNull('end_time')
            ->firstOrFail();

        $attendance->update([
            'end_time' => now(),
            'status' =>'finished',
        ]);

        return redirect()->route('user.attendance.index');
    }


    public function list()
    {
        $user = auth()->user();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->orderBy('work_date', 'desc')
            ->get();

        return view('attendance.list', [
            'attendances' => $attendances,
        ]);
    }

    public function show(Attendance $attendance)
    {
        $attendance->load([
            'breaks',
            'correctionRequests' => function ($query) {
                $query->latest();
            }
        ]);

        $isPending = $attendance->correctionRequests
            ->first()?->status === 'pending';

        return view('attendance.show', [
            'attendance' => $attendance,
            'breaks'     => $attendance->breaks,
            'isPending'  => $isPending,
        ]);
    }

    public function storeCorrection(Request $request, Attendance $attendance){
        $request->validate([
            'work_start' => ['required'],
            'work_end' => ['required'],
            'breaks.*.start' => ['nullable'],
            'breaks.*.end' => ['nullable'],
            'remark' => ['nullable', 'string'],
        ]);

        //修正申請を保存
        $correction = CorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => auth()->id(),
            'work_start' => $request->work_start,
            'work_end' => $request->work_end,
            'remark' => $request->remark,
            'status' => 'pending',//承認待ち
        ]);

        //休憩修正（複数）
        foreach ($request->breaks ?? [] as $break) {
            if (empty($break['start']) || empty($break['end'])) {
                continue;
            }

            $correction->breakCorrections()->create([
                'start_time' => $break['start'],
                'end_time' => $break['end'],
            ]);
        }
        return redirect()
            ->route('user.attendance.show', $attendance->id)
            ->with('message', '修正申請を送信しました');
    }
}
