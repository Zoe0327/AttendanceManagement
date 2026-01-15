@extends('layouts.admin')
@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/staff.css') }}">
@endsection

@section('content')
<div class="admin-staff-list">
    <h2 class="admin-staff-list__title">スタッフ一覧</h2>

    <table class="admin-staff-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>
                        {{ $user->name }}
                    </td>
                    <td>
                        {{ $user->email }}
                    </td>
                    <td>
                        <a href="{{ route('admin.attendance.staff', $user->id) }}">詳細</a>
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