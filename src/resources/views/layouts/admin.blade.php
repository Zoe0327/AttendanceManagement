<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AttendanceManagement')</title>
    <link rel="stylesheet" href="{{ asset('css/layouts/admin.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @yield('css')
    @yield('js')
</head>
<body class="admin-body">
    <header class="admin-header">
        <div class="admin-header__inner">
            <div class="admin-header-utilities">
                {{-- ロゴ --}}
                <a href="{{ route('admin.attendance.list') }}" class="admin-header__logo">
                    <img src="{{ asset('storage/item_images/CoachTech_White.png') }}" alt="COACHTECH" class="admin-header__logo-img">
                </a>
                <div class="admin-header__nav-wrap" id="js-nav-wrap">
                    <nav>
                        <ul class="admin-header-nav">
                            <li class="admin-header-nav__item">
                                <a class="admin-header-nav__link" href="{{ route('admin.attendance.list') }}">
                                    勤怠一覧
                                </a>
                            </li>

                            <li class="admin-header-nav__item">
                                <a class="admin-header-nav__link" href="{{ route('admin.staff.list') }}">
                                    スタッフ一覧
                                </a>
                            </li>

                            <li class="admin-header-nav__item">
                                <a class="admin-header-nav__link" href="{{ route('admin.request.index') }}">
                                    申請一覧
                                </a>
                            </li>

                            <li class="admin-header-nav__item">
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button class="admin-logout" type="submit">ログアウト</button>
                                </form>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <main class="admin-main">
        @yield('content')
    </main>
</body>
</html>
