@extends('layouts.admin')
@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/request/show.css') }}">
@endsection

@section('content')
<div class="admin-request__show-detail">
    <h2 class="admin-request__show-detail__title">勤怠詳細</h2>

    <div class="admin-request__show-detail__content">
        <form method="POST" action="{{ route('admin.request.approve', $request->id) }}">
            @csrf
            @method('PUT')
            <div class="admin-request__show-detail__table-wrapper">
                <table class="admin-request__show-detail__table">
                    <tbody>
                        <tr>
                            <th>名前</th>
                            <td class="admin-request__show-name-cell">
                                <span class="admin-request__show-name__user">{{ $request->attendance->user->name }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>日付</th>
                            <td class="admin-request__show-date-cell">
                                <span class="admin-request__show-date-year">
                                    {{ Carbon\Carbon::parse($request->attendance->work_date)->format('Y年') }}
                                </span>
                                <span class="admin-request__show-date-md">
                                    {{ Carbon\Carbon::parse($request->attendance->work_date)->format('n月j日') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>出勤・退勤</th>
                            <td>
                                {{ $request->requested_start_time->format('H:i') }}
                                <span class="admin-request__show-time-separator">～</span>
                                {{ $request->requested_end_time->format('H:i') }}
                            </td>
                        </tr>
                        @foreach ($request->breakCorrections as $index => $break)
                        <tr>
                            <th>休憩{{ $index + 1 }}</th>
                            <td>
                                {{ $break->start_time->format('H:i') }}
                                ～
                                {{ $break->end_time->format('H:i') }}
                            </td>
                        </tr>
                        @endforeach
                        <tr>
                            <th>備考</th>
                            <td>
                                {{ $request->reason }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="admin-request__show-detail__action">
                <button type="submit" class="admin-request__show-detail__btn">承認</button>
            </div>
        </form>
    </div>
</div>
@endsection