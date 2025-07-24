<!DOCTYPE html>
<html lang="jp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>attendance_management</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" />
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header-logo">
            <img src="{{ asset('storage/image/logo.svg') }}" alt="coachtech">
        </div>
        <form class="nav-links" action="/logout" method="post">
        @csrf
            <a href="/attendance/list" class="btn">勤怠一覧</a>
            <a href="" class="btn">スタッフ一覧</a>
            <a href="#" class="btn">申請一覧</a>
            <button class="button" type="submit">ログアウト</button>
        </form>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>