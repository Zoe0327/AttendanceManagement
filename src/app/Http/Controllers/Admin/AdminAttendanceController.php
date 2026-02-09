<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UpdateAttendanceRequest;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
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

        $isPending = !is_null($latestCorrection);

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

    public function exportCsv(Request $request, $id): StreamedResponse
    {
        $user = User::findOrFail($id);

        $month = $request->query('month')
        ? Carbon::createFromFormat('Y-m', $request->query('month'))
        : now();
        
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->orderBy('work_date')
            ->get();

        $fileName = sprintf(
            '%s_%s_attendance.csv',
            $user->name,
            $month->format('Y_m')
        );

        //CSVレスポンス
        return response()->streamDownload(function () use ($attendances) {
            $handle = fopen('php://output', 'w');

            // BOM(Excel文字化け対策)
            fwrite($handle, "\xEF\xBB\xBF");

            //ヘッダー行
            fputcsv($handle, [
                '日付',
                '出勤',
                '退勤',
                '休憩',
                '合計',
            ]);

            //データ行
            foreach ($attendances as $attendance) {
                fputcsv($handle, [
                    $attendance->work_date->format('Y/m/d'),
                    optional($attendance->start_time)->format('H:i'),
                    optional($attendance->end_time)->format('H:i'),
                    $attendance->total_break_time,
                    $attendance->total_work_time,
                ]);
            }

            fclose($handle);

        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
