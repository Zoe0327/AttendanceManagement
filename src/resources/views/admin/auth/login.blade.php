@extends('layouts.auth')
@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/auth/login.css') }}">
@endsection

@section('content')
<div class="admin-login-form__content">
    <div class="admin-login-form__heading">
        <h2>管理者ログイン</h2>
    </div>
    <form class="admin__form" action="{{ route('admin.authenticate') }}" method="post">
        @csrf
        <div class="admin-form__group">
            <div class="admin-form__group-title">
                <span class="admin-form__label--item">メールアドレス</span>
            </div>
            <div class="admin-form__group-content">
                <div class="admin-form__input--text">
                    <input type="email" name="email" value="{{ old('email') }}" required />
                </div>
                <div class="admin-form__error">
                    @error('email')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>
        <div class="admin-form__group">
            <div class="admin-form__group-title">
                <span class="admin-form__label--item">パスワード</span>
            </div>
            <div class="admin-form__group-content">
                <div class="admin-form__input--text">
                    <input type="password" name="password" required />
                </div>
                <div class="admin-form__error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>
        <div class="admin-form__group--button">
            <div class="admin-form__button">
                <button class="admin-form__button-submit" type="submit">管理者ログインする</button>
            </div>
        </div>
    </form>
</div>
@endsection