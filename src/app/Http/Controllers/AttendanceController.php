<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\BreakTime;
use App\Http\Requests\Attendance\StoreCorrectionRequest;

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
            ->exists();

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

        $isOnBreak = $attendance->breaks()
            ->whereNull('end_time')
            ->exists();

        if ($isOnBreak) {
            return redirect()->route('user.attendance.index');
        }

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


    public function list(Request $request)
    {
        $user = auth()->user();

        //表示する月
        $currentMonth = $request->query('month')
            ? Carbon::parse($request->query('month'))
            : Carbon::now();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('work_date', $currentMonth->year)
            ->whereMonth('work_date', $currentMonth->month)
            ->orderBy('work_date')
            ->get();

        return view('attendance.list', [
            'attendances' => $attendances,
            'currentMonth' => $currentMonth,
        ]);
    }

    public function show(Attendance $attendance)
    {
        $attendance->load([
            'user',
            'breaks',
            'correctionRequests.breakCorrections',
        ]);

        $latestCorrection = $attendance->correctionRequests
            ->where('status', 0)
            ->sortByDesc('created_at')
            ->first();

        $isPending = $latestCorrection !== null;

        return view('attendance.show', [
            'attendance' => $attendance,
            'breaks'     => $attendance->breaks,
            'isPending'  => $isPending,
            'latestCorrection' => $latestCorrection
        ]);
    }

    public function storeCorrection(StoreCorrectionRequest $request, Attendance $attendance){
        //勤務日
        $workDate = $attendance->work_date->format('Y-m-d');
        //出勤・退勤をdatetimeに変換
        $requestedStart = Carbon::createFromFormat(
            'Y-m-d H:i',
            $workDate . ' ' . $request->work_start
        );
        $requestedEnd = Carbon::createFromFormat(
            'Y-m-d H:i',
            $workDate . ' ' . $request->work_end
        );
        //修正申請を保存
        $correction = CorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => auth()->id(),
            'requested_start_time' => $requestedStart,
            'requested_end_time' => $requestedEnd,
            'reason' => $request->remark,
            'status' => 0,//承認待ち
        ]);

        //休憩修正（複数）
        foreach ($request->breaks ?? [] as $break) {
            if (empty($break['start']) || empty($break['end'])) {
                continue;
            }

            $correction->breakCorrections()->create([
                'start_time' => Carbon::createFromFormat(
                    'Y-m-d H:i',
                    $workDate . ' ' . $break['start']
                ),
                'end_time' => Carbon::createFromFormat(
                    'Y-m-d H:i',
                    $workDate . ' ' . $break['end']
                ),
            ]);
        }
        return redirect()
            ->route('user.attendance.show', $attendance->id)
            ->with('message', '修正申請を送信しました');
    }
}
