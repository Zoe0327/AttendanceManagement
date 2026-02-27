<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AttendanceManagement')</title>
    <link rel="stylesheet" href="{{ asset('css/layouts/auth.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @yield('css')
</head>
<body class="auth-body">
    <header class="auth-header">
        <div class="auth-header__inner">
            <div class="auth-header-utilities">
                {{-- ロゴ --}}
                <a href="{{ route('login') }}" class="auth-header__logo">
                    <img src="{{ asset('images/CoachTech_White.png') }}" alt="COACHTECH" class="auth-header__logo-img">
                </a>
            </div>
        </div>
    </header>

    <main class="auth-main">
        @yield('content')
    </main>
</body>
</html>
