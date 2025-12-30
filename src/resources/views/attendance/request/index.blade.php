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
        <tbody>
            <tr>
                <td>承認待ち</td>
                <td>山田太郎</td>
                <td>2023/06/01</td>
                <td>遅延のため</td>
                <td>2023/06/02</td>
                <td><a href="#">詳細</a></td>
            </tr>
        </tbody>
    </table>
</div>

@endsection