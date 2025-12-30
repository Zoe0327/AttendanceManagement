@extends('layouts.user')
@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h2 class="attendance-detail__title">勤怠詳細</h2>

    <div class="attendance-detail__content">
        <form method="POST" action="#">
            @csrf

            <table class="attendance-detail__table">
                <tbody>
                    <tr>
                        <th>名前</th>
                        <td>山田 太郎</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td class="date-cell">
                            <span class="date-year">2023年</span>
                            <span class="date-md">6月1日</span>
                        </td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            <input type="time" name="work_start" value="09:00">
                            <span class="time-separator">～</span>
                            <input type="time" name="work_end" value="18:00">
                        </td>
                    </tr>
                    <tr>
                        <th>休憩</th>
                        <td>
                            @foreach ($breaks as $index => $break)
                                <div class="break-row">
                                    <input type="time" name="breaks[{{ $index }}][start]" value="{{ $break->start_time }}">
                                    <span class="time-separator">～</span>
                                    <input type="time" name="breaks[{{ $index }}][end]" value="{{ $break->end_time }}">
                                </div>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th>備考</th>
                        <td>
                            <textarea name="remark" class="attendance-detail__text" rows="4"></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>

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