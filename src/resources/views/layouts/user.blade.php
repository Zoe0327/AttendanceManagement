<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AttendanceManagement')</title>
    <link rel="stylesheet" href="{{ asset('css/layouts/user.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @yield('css')
    @yield('js')
</head>
<body class="user-body">
    <header class="user-header">
        <div class="user-header__inner">
            <div class="user-header-utilities">
                {{-- ロゴ --}}
                <a href="{{ route('user.attendance.list') }}" class="user-header__logo">
                    <img src="{{ asset('storage/item_images/CoachTech_White.png') }}" alt="COACHTECH" class="user-header__logo-img">
                </a>

                <div class="user-header__nav-wrap" id="js-nav-wrap">
                    <nav>
                        <ul class="user-header-nav">

                            {{-- 退勤後かどうか判定 --}}
                            @if (Route::is('user.attendance.index') && isset($attendance) && $attendance->end_time !== null)
                                {{-- 退勤後 --}}
                                <li class="user-header-nav__item">
                                    <a class="user-header-nav__link" href="{{ route('user.attendance.list') }}"> 今月の出勤一覧 </a>
                                </li>
                                <li class="user-header-nav__item">
                                    <a class="user-header-nav__link" href="{{ route('user.request.index') }}">
                                        申請一覧
                                    </a>
                                </li>
                            @else
                                {{-- ⏳ 退勤前 --}}
                                <li class="user-header-nav__item">
                                    <a class="user-header-nav__link" href="{{ route('user.attendance.index') }}">
                                        勤怠
                                    </a>
                                </li>

                                <li class="user-header-nav__item">
                                    <a class="user-header-nav__link" href="{{ route('user.attendance.list') }}">
                                        勤怠一覧
                                    </a>
                                </li>

                                <li class="user-header-nav__item">
                                    <a class="user-header-nav__link" href="{{ route('user.request.index') }}">
                                        申請
                                    </a>
                                </li>

                            @endif
                            <li class="user-header-nav__item">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="user-logout" type="submit">ログアウト</button>
                                </form>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <main class="user-main">
        @yield('content')
    </main>
</body>
</html>
