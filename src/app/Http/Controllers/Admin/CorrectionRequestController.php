<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;

class CorrectionRequestController extends Controller
{
    //修正申請一覧（管理者）
    public function index()
    {
        $query = CorrectionRequest::with(['attendance.user']);

        $pendingRequests = (clone $query)
            ->where('status', 0)
            ->latest()
            ->get();

            $approvedRequests = (clone $query)
            ->where('status',1)
            ->latest()
            ->get();

        return view(
        'admin.request.index',
        compact('pendingRequests', ('approvedRequests')));
    }

    //修正申請詳細（管理者）
    public function show($id)
    {
        $request = CorrectionRequest::with([
            'attendance.user',
            'breakCorrections'
        ])->findOrFail($id);

        return view('admin.request.show', compact('request'));
    }

    public function approve($id)
    {
        DB::transaction(function () use ($id){
            //①修正申請取得
            $correctionRequest = CorrectionRequest::with([
                'attendance',
                'breakCorrections'
            ])->findOrFail($id);
            $attendance = $correctionRequest->attendance;

            //②勤怠本体を申請内容で更新
            $attendance->update([
                'start_time' => $correctionRequest->requested_start_time,
                'end_time' => $correctionRequest->requested_end_time,
                'remark' => $correctionRequest->reason,
                'status' => 1,//承認済み
            ]);

            //③休憩は一度削除して再作成
            $attendance->breaks()->delete();

            foreach($correctionRequest->breakCorrections as $break) {
                $attendance->breaks()->create([
                    'start_time' => $break->start_time,
                    'end_time' => $break->end_time,
                ]);
            }

            //修正申請を承認済みに
            $correctionRequest->update([
                'status' => 1,
                'approved_at' =>now(),
            ]);
        });

        return redirect()
            ->route('admin.request.index')
            ->with('message', '申請を承認しました');
    }
}