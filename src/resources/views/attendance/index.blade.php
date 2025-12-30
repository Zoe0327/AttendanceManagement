@extends('layouts.user')
@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')
<div class="attendance-form__content">
    <div class="attendance-status">
        @if ($status === 'off_duty')
            <p class="status__off-duty">勤務外</p>
        @elseif ($status === 'working')
            <p class="status__at-work">勤務中</p>
        @elseif ($status === 'on_break')
            <p class="status__at-work">休憩中</p>
        @endif
    </div>
    <div class="attendance-date">
        <h2 class="attendance-date__show">{{ \Carbon\Carbon::now()->translatedFormat('Y年m月d日（D）') }}</h2>
    </div>
    <div class="attendance-time">
        <h1 class="attendance-time__show">{{ $time }}</h1>
    </div>
    <div class="attendance-status__button">
        @if ($status === 'off_duty')
        <form action="{{ route('user.attendance.start') }}" method="post">
            @csrf
            <button class="status__button-start">出勤</button>
        </form>
        @elseif ($status === 'working')
        <form action="{{ route('user.attendance.end') }}" method="post">
            @csrf
            <button class="status__button-end">退勤</button>
        </form>
        <form action="{{ route('user.attendance.break.start') }}" method="post">
            @csrf
            <button class="status__button-break-start">休憩入</button>
        </form>
        @elseif($status === 'on_break')
        <form action="{{ route('user.attendance.break.end') }}" method="post">
            @csrf
            <button class="status__button-break-end">休憩戻</button>
        </form>
        @endif
    </div>
    @if ($status === 'finished')
        <div class="attendance-status__end">
            <p class="status__end-comment">お疲れ様でした。</p>
        </div>
    @endif
</div>
@endsection
