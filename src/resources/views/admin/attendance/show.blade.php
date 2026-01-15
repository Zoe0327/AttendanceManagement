@extends('layouts.admin')
@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/show.css') }}">
@endsection

@section('content')
<div class="admin-attendance-detail">
    <h2 class="admin-attendance-detail__title">勤怠詳細</h2>

    <div class="admin-attendance-detail__content">
        <form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
            @csrf
            @method('PUT')
            <div class="admin-attendance-detail__table-wrapper">
                <table class="admin-attendance-detail__table">
                    <tbody>
                        <tr>
                            <th>名前</th>
                            <td>{{ $attendance->user->name }}</td>
                        </tr>
                        <tr>
                            <th>日付</th>
                            <td class="admin-date-cell">
                                <span class="admin-date-year">
                                    {{ Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}
                                </span>
                                <span class="admin-date-md">
                                    {{ Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>出勤・退勤</th>
                            <td>
                                @if ($isPending)
                                    {{ $latestCorrection->requested_start_time->format('H:i') }}
                                    <span class="admin-time-separator">～</span>
                                    {{ $latestCorrection->requested_end_time->format('H:i') }}
                                @else
                                    <input type="time" name="work_start"
                                    value="{{ $attendance->start_time?->format('H:i') }}">
                                    <span class="admin-time-separator">～</span>
                                    <input type="time" name="work_end"
                                    value="{{ $attendance->end_time?->format('H:i') }}">
                                @endif
                            </td>
                        </tr>
                        @if ($isPending)
                            @foreach ($latestCorrection->breakCorrections as $index => $break)
                                <tr>
                                    <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                                    <td>
                                        {{ $break->start_time->format('H:i') }}
                                        <span class="admin-time-separator">～</span>
                                        {{ $break->end_time->format('H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            @foreach ($breaks as $index => $break)
                                <tr>
                                    <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                                    <td>
                                        <input type="time" name="breaks[{{ $index }}][start]"
                                            value="{{ $break->start_time?->format('H:i') }}">
                                        <span class="admin-time-separator">～</span>
                                        <input type="time" name="breaks[{{ $index }}][end]"
                                        value="{{ $break->end_time?->format('H:i') }}">
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        <tr>
                            <th>備考</th>
                            <td>
                                @if ($isPending)
                                    {{ $latestCorrection->reason }}
                                @else
                                    <textarea name="remark" class="admin-attendance-detail__text" rows="4"></textarea>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="admin-attendance-detail__action">
                @if ($isPending)
                    <p class="admin-attendance-detail__notice">
                        ※ 承認待ちのため修正できません。
                    </p>
                @else
                    <button type="submit" class="admin-attendance-detail__btn">修正</button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection