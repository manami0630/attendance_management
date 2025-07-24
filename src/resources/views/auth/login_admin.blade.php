<!DOCTYPE html>
<html lang="jp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>attendance_management</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/login_admin.css') }}" />
</head>
<body>
    <header>
        <img src="{{ asset('storage/image/logo.svg') }}" alt="coachtech">
    </header>
    <main>
        <form class="form" action="/admin/login" method="post">
        @csrf
            <div class="form__heading">
                <h2>管理者ログイン</h2>
            </div>
            <div class="form__group">
                <label class="login-form__label" for="email">メールアドレス</label>
                <input class="login-form__input" type="email" name="email" id="email" value="{{ old('email') }}">
                <div class="form__error">
                    @error('email')
                    {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="form__group">
                <label class="login-form__label" for="password">パスワード</label>
                <input class="login-form__input" type="password" name="password" id="password">
                <div class="form__error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>
            <div class="form__group">
                <input class="login-form__btn" type="submit" value="管理者ログインする">
            </div>
        </form>
    </main>
</body>
</html>