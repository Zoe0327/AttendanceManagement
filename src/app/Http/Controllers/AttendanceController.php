<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Illuminate\Support\Facades\DB;
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

        // 表示する月
        $currentMonth = $request->query('month')
            ? Carbon::parse($request->query('month'))
            : Carbon::now();

        // ▼ 月初〜月末を取得
        $start = $currentMonth->copy()->startOfMonth();
        $end   = $currentMonth->copy()->endOfMonth();

        // ▼ 月の全日付を生成
        $dates = [];
        while ($start <= $end) {
            $dates[] = $start->copy();
            $start->addDay();
        }

        // ▼ その月の勤怠を取得して日付キーにする
        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('work_date', $currentMonth->year)
            ->whereMonth('work_date', $currentMonth->month)
            ->get()
            ->keyBy(function ($item) {
                return \Carbon\Carbon::parse($item->work_date)->toDateString();
            });

        return view('attendance.list', [
            'dates' => $dates,
            'attendances' => $attendances,
            'currentMonth' => $currentMonth,
        ]);
    }

    public function show($date)
    {
        $user = auth()->user();

        try {
            $date = Carbon::createFromFormat('Y-m-d', $date)->toDateString();
        } catch (\Exception $e) {
            abort(404);
        }

        $attendance = Attendance::firstOrCreate(
            [
                'user_id'   => $user->id,
                'work_date' => $date,
            ],
            [
                'start_time' => null,
                'end_time'   => null,
                'remark'     => null,
            ]
        );

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
            'date'       => $date,
            'breaks'     => $attendance->breaks ?? collect(),
            'isPending'  => $isPending,
            'latestCorrection' => $latestCorrection
        ]);
    }

    public function storeCorrection(StoreCorrectionRequest $request, $date)
    {
        $user = auth()->user();

        try {
            $date = Carbon::createFromFormat('Y-m-d', $date)->toDateString();
        } catch (\Exception $e) {
            abort(404);
        }

        $attendance = Attendance::firstOrCreate([
            'user_id'   => $user->id,
            'work_date' => $date,
        ]);

        $workDate = $date;

        $requestedStart = Carbon::createFromFormat('Y-m-d H:i', $workDate . ' ' . $request->work_start);
        $requestedEnd   = Carbon::createFromFormat('Y-m-d H:i', $workDate . ' ' . $request->work_end);

        // breaks：start/end 両方ある行だけ採用（空欄行は無視）
        $breakInputs = collect($request->input('breaks', []))
            ->filter(function ($b) {
                $s = $b['start'] ?? null;
                $e = $b['end'] ?? null;
                return !empty($s) && !empty($e);
            })
            ->values();

        DB::transaction(function () use ($attendance, $user, $requestedStart, $requestedEnd, $request, $breakInputs) {

            $correction = CorrectionRequest::create([
                'attendance_id'        => $attendance->id,
                'user_id'              => $user->id,
                'requested_start_time' => $requestedStart,
                'requested_end_time'   => $requestedEnd,
                'reason'               => $request->remark,
                'status'               => 0,
            ]);

            // ✅ break_corrections に保存
            foreach ($breakInputs as $b) {
                $correction->breakCorrections()->create([
                    'start_time' => $b['start'], // 'H:i'
                    'end_time'   => $b['end'],   // 'H:i'
                ]);
            }
        });

        return redirect()
            ->route('user.attendance.show', $date)
            ->with('message', '修正申請を送信しました');
    }
}
