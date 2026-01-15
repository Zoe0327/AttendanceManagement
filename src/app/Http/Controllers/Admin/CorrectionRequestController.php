<?php

namespace App\Http\Controllers\Admin;

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
        $correctionRequest = CorrectionRequest::findOrFail($id);

        $correctionRequest->update([
            'status' => 1,//承認済み
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('admin.request.index')
            ->with('message', '申請を承認しました');
    }
}
