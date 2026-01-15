@extends('layouts.user')
@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h2 class="attendance-detail__title">勤怠詳細</h2>

    <div class="attendance-detail__content">
        <form method="POST" action="{{ route('user.attendance.correction.store', $attendance->id) }}">
            @csrf

            <div class="attendance-detail__table-wrapper">
                <table class="attendance-detail__table">
                    <tbody>
                        <tr>
                            <th>名前</th>
                            <td>山田 太郎</td>
                        </tr>
                        <tr>
                            <th>日付</th>
                            <td class="date-cell">
                                <span class="date-year">
                                    {{ Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}
                                </span>
                                <span class="date-md">
                                    {{ Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>出勤・退勤</th>
                            <td>
                                @if ($isPending)
                                    {{ $latestCorrection->requested_start_time->format('H:i') }}
                                    <span class="time-separator">～</span>
                                    {{ $latestCorrection->requested_end_time->format('H:i') }}
                                @else
                                    <input type="time" name="work_start" value="{{ $attendance->start_time?->format('H:i') }}">
                                    <span class="time-separator">～</span>
                                    <input type="time" name="work_end" value="{{ $attendance->end_time?->format('H:i') }}">
                                @endif
                            </td>
                        </tr>
                        @if ($isPending)
                            @foreach ($latestCorrection->breakCorrections as $index => $break)
                                <tr>
                                    <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                                    <td>
                                        {{ $break->start_time->format('H:i') }}
                                        <span class="time-separator">～</span>
                                        {{ $break->end_time->format('H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            @forelse ($breaks as $index => $break)
                                <tr>
                                    <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                                    <td>
                                        <input type="time" name="breaks[{{ $index }}][start]" value="{{ $break->start_time?->format('H:i') }}">
                                        <span class="time-separator">～</span>
                                        <input type="time" name="breaks[{{ $index }}][end]" value="{{ $break->end_time?->format('H:i') }}">
                                    </td>
                                </tr>
                            @empty
                                {{--休憩が1件もない場合：新規入力用--}}
                            <tr>
                                <th>休憩</th>
                                <td>
                                    <input type="time" name="breaks[0][start]">
                                    <span class="time-separator">～</span>
                                    <input type="time" name="breaks[0][end]">
                                </td>
                            </tr>
                            @endforelse
                        @endif
                        <tr>
                            <th>備考</th>
                            <td>
                                @if ($isPending)
                                    {{ $latestCorrection->reason }}
                                @else
                                    <textarea name="remark" class="attendance-detail__text" rows="4"></textarea>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="attendance-detail__action">
                @if ($isPending)
                    <p class="attendance-detail__notice">
                        ※ 承認待ちのため修正できません。
                    </p>
                @else
                    <button type="submit" class="attendance-detail__btn">修正</button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection