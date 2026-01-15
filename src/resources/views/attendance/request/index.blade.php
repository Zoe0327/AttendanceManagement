@extends('layouts.user')
@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/request/index.css') }}">
@endsection

@section('content')
<div class="attendance-request">
    <h2 class="attendance-request__title">申請一覧</h2>

    <div class="attendance-request__tabs">
        <h4 class="request__tab request__tab--active" data-target="waiting-approval">承認待ち</h4>
        <h4 class="request__tab" data-target="approved">承認済み</h4>
    </div>

    <table class="attendance-request__table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody id="waiting-approval">
        @forelse ($pendingRequests as $request)
            <tr>
                <td>承認待ち</td>
                <td>{{ $request->attendance->user->name }}</td>
                <td>{{ $request->attendance->work_date->format('Y/m/d') }}</td>
                <td>{{ $request->reason }}</td>
                <td>{{ $request->created_at->format('Y/m/d') }}</td>
                <td><a href="{{ route('user.attendance.show', $request->attendance_id) }}">詳細</a></td>
            </tr>
        @empty
            <tr>
                <td colspan="6">承認待ちの申請はありません</td>
            </tr>
        @endforelse
        </tbody>

        <tbody id="approved" style="display: none;">
            @forelse ($approvedRequests as $request)
                <tr>
                    <td>承認済み</td>
                    <td>{{ $request->attendance->user->name }}</td>
                    <td>
                        {{ $request->attendance->work_date->format('Y/m/d') }}
                    </td>
                    <td>{{ $request->reason }}</td>
                    <td>
                        {{ $request->created_at->format('Y/m/d') }}
                    </td>
                    <td>
                        <a href="{{ route('user.attendance.show', $request->attendance_id) }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">承認済みの申請はありません</td>
                </tr>
            @endforelse
            </tbody>
    </table>
</div>

@endsection