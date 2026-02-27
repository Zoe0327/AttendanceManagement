@extends('layouts.admin')
@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/index.css') }}">
@endsection

@section('content')
<div class="admin-attendance-list">
    <h2 class="admin-attendance-list__title"> {{ $date->format('Y年n月j日') }}の勤怠</h2>

    <div class="admin-attendance-list__month">
        <a href="{{ route('admin.attendance.list', ['date' => $date->copy()->subDay()->toDateString()]) }}">
            <img src="{{ asset('images/arrow.png') }}" alt="arrow" class="admin-attendance-list__left-arrow-img">
            前日
        </a>
        <span>
            <img src="{{ asset('images/calendar.png') }}" alt="calendar" class="admin-attendance-list__month-img">
            {{ $date->format('Y/m/d') }}
        </span>
        <a href="{{ route('admin.attendance.list', ['date' => $date->copy()->addDay()->toDateString()]) }}">
            翌日
            <img src="{{ asset('images/arrow.png') }}" alt="arrow" class="admin-attendance-list__right-arrow-img">
        </a>
    </div>
    <table class="admin-attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
                <tr>
                    <td>
                        {{ $attendance->user->name }}
                    </td>
                    <td>
                        {{ $attendance->start_time?->format('H:i') }}
                    </td>
                    <td>
                        {{ $attendance->end_time?->format('H:i') }}
                    </td>
                    <td>
                        {{ $attendance->total_break_time }}
                    </td>
                    <td>
                        {{ $attendance->total_work_time }}
                    </td>
                    <td>
                        <a href="{{ route('admin.attendance.show', $attendance->id) }}">詳細</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">勤怠データがありません</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection