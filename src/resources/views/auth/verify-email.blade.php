<!DOCTYPE html>
<html lang="jp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>attendance_management</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/verify-email.css') }}" />
</head>
<body>
    <header>
        <img src="{{ asset('storage/image/logo.svg') }}" alt="coachtech">
    </header>
    <main>
        <p>登録していただいたメールアドレスに認証メールを送付しました。</br>メール認証を完了してください。</p>
        <a href="http://localhost:8025">認証はこちらから</a>
        @if (session('status') == 'verification-link-sent')
            <p style="color: green;">認証メールを再送信しました。</p>
        @endif
        <form method="POST" action="{{ route('verification.send') }}">
        @csrf
            <button type="submit">認証メールを再送信</button>
        </form>
    </main>
</body>
</html>
