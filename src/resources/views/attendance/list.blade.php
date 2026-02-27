@extends('layouts.user')
@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h2 class="attendance-list__title">勤怠一覧</h2>

    <div class="attendance-list__month">
        <a href="{{ route('user.attendance.list', ['month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}">
            <img src="{{ asset('images/arrow.png') }}" alt="arrow" class="attendance-list__left-arrow-img">
            前月
        </a>
        <span>
            <img src="{{ asset('images/calendar.png') }}" alt="calendar" class="attendance-list__month-img">
            {{ $currentMonth->format('Y/m') }}
        </span>
        <a href="{{ route('user.attendance.list', ['month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}">
            翌月
            <img src="{{ asset('images/arrow.png') }}" alt="arrow" class="attendance-list__right-arrow-img">
        </a>
    </div>
    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dates as $date)
                @php
                    $attendance = $attendances[$date->toDateString()] ?? null;
                @endphp

                <tr>
                    <td>{{ $date->translatedFormat('m/d (D)') }}</td>
                    <td>
                        {{ $attendance?->start_time
                            ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i')
                            : '' }}
                    </td>
                    <td>
                        {{ $attendance?->end_time
                            ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i')
                            : '' }}
                    </td>

                    <td>{{ $attendance?->total_break_time }}</td>
                    <td>{{ $attendance?->total_work_time }}</td>

                    <td>
                        <a href="{{ route('user.attendance.show', $date->toDateString()) }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection