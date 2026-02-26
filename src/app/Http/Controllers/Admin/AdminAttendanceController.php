<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UpdateAttendanceRequest;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            ->where('status', 0)
            ->latest()
            ->first();

        return view('admin.attendance.show', [
            'attendance' => $attendance,
            'breaks' => $attendance->breaks,
            'latestCorrection' => $latestCorrection,
            'isPending' => !is_null($latestCorrection),
        ]);
    }

    public function showByDate(string $date, Request $request)
    {
        $userId = $request->query('user_id');
        abort_unless($userId, 404);

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $userId, 'work_date' => $date],
            ['status' => Attendance::STATUS_ADMIN]
        );

        $attendance->load(['user', 'breaks', 'correctionRequests.breakCorrections']);

        $latestCorrection = $attendance->correctionRequests()
            ->where('status', 0)
            ->latest()
            ->first();

        return view('admin.attendance.show', [
            'attendance' => $attendance,
            'breaks' => $attendance->breaks,
            'latestCorrection' => $latestCorrection,
            'isPending' => !is_null($latestCorrection),
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

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $dates = [];
        while ($start <= $end) {
            $dates[] = $start->copy();
            $start->addDay();
        }

        $attendances = $user->attendances()
            ->whereYear('work_date', $month->year)
            ->whereMonth('work_date', $month->month)
            ->get()
            ->keyBy(fn ($a) => $a->work_date->toDateString());

        return view('admin.staff.index', [
            'user' => $user,
            'month' => $month,
            'dates' => $dates,
            'attendances' => $attendances,
        ]);
    }

    public function exportCsv(Request $request, $id): StreamedResponse
    {
        $user = User::findOrFail($id);

        $month = $request->query('month')
            ? Carbon::createFromFormat('Y-m', $request->query('month'))->startOfMonth()
            : now()->startOfMonth();

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth   = $month->copy()->endOfMonth();

        // 月初〜月末の全日付を生成
        $dates = CarbonPeriod::create($startOfMonth, $endOfMonth);

        // 勤怠は work_date をキーにして取得（全日付ループで参照しやすくする）
        $attendancesByDate = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->work_date)->toDateString());

        $fileName = sprintf(
            '%s_%s_attendance.csv',
            $user->name,
            $month->format('Y_m')
        );

        return response()->streamDownload(function () use ($dates, $attendancesByDate) {
            $handle = fopen('php://output', 'w');

            // BOM(Excel文字化け対策)
            fwrite($handle, "\xEF\xBB\xBF");

            // ヘッダー行
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            // ✅ 全日付で出力
            foreach ($dates as $date) {
                $key = $date->toDateString(); // "Y-m-d"
                $attendance = $attendancesByDate->get($key); // Attendance|null

                fputcsv($handle, [
                    $date->format('Y/m/d'),
                    $attendance?->start_time?->format('H:i') ?? '',
                    $attendance?->end_time?->format('H:i') ?? '',
                    $attendance?->total_break_time ?? '',
                    $attendance?->total_work_time ?? '',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
