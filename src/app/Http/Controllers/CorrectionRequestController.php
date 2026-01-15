<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequest;


class CorrectionRequestController extends Controller
{
    public function index()
    {
        $query = CorrectionRequest::with(['attendance'])
            ->whereHas('attendance', function ($q) {
                $q->where('user_id', auth()->id());
        });

        $pendingRequests = (clone $query)
            ->where('status', 0)//承認待ち
            ->latest()
            ->get();

        $approvedRequests = (clone $query)
            ->where('status', 1)
            ->latest()
            ->get();

        return view(
            'attendance.request.index',
            compact('pendingRequests', 'approvedRequests')
        );
    }
}
