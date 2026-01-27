@extends('layouts.auth')
@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css')}}">
@endsection

@section('content')
<div class="verify-email__content">
    <div class="verify-email__heading">
        <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
        <p>メール認証を完了してください</p>
    </div>

    <div class="verify-email__form">
        <a href="https://mailtrap.io/home" target="_blank" class="verify-email__button-submit">
            認証はこちらから
        </a>
    </div>

    <form class="verify-email__form" action="{{ route('verification.send') }}" method="POST">
        @csrf
        <div class="verify-email__link">
            <button class="verify-email__link-button" type="submit">
                認証メールを再送する
            </button>
        </div>
    </form>
</div>
@endsection