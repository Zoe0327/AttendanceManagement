@extends('layouts.admin')
@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/index.css') }}">
@endsection

@section('content')
<div class="staff-list">
    <h2 class="staff-list__title">{{ $user->name }}さんの一覧</h2>

    <div class="staff-list__month">
        <a href="{{ route('admin.attendance.staff', [
            'id' => $user->id,
            'month' => $month->copy()->subMonth()->format('Y-m')]) }}">
            <img src="{{ asset('storage/item_images/arrow.png') }}" alt="arrow" class="staff-list__left-arrow-img">
            前月
        </a>
        <span>
            <img src="{{ asset('storage/item_images/calender.png') }}" alt="calender" class="staff-list__month-img">
            {{ $month->format('Y/m') }}
        </span>
        <a href="{{ route('admin.attendance.staff', [
            'id' =>$user->id,
            'month' => $month->copy()->addMonth()->format('Y-m')]) }}">
            翌月
            <img src="{{ asset('storage/item_images/arrow.png') }}" alt="arrow" class="staff-list__right-arrow-img">
        </a>
    </div>
    <table class="staff-table">
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
            @forelse ($attendances as $attendance)
                <tr>
                    <td>
                        {{ $attendance->work_date->translatedFormat('m/d (D)') }}
                    </td>
                    <td>
                        {{ $attendance->start_time?->format('H:i') ?? '-' }}
                    </td>
                    <td>
                        {{ $attendance->end_time?->format('H:i') ?? '-' }}
                    </td>
                    <td>
                        {{ $attendance->total_break_time }}
                    </td>
                    <td>
                        {{ $attendance->total_work_time }}
                    </td>
                    <td>
                        <a href="{{ route('admin.attendance.show',$attendance->id) }}">詳細</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">勤怠データがありません</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="staff-data__export">
        <a href="{{ route('admin.attendance.staff.csv', [
            'id' => $user->id,
            'month' => $month->format('Y-m')
        ]) }}">
        CSV出力
        </a>
    </div>
</div>



@endsection