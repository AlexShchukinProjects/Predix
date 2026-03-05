<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сброс пароля</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
        .wrapper { max-width: 600px; margin: 0 auto; padding: 20px; }
        .content { background: #fff; padding: 30px; border-radius: 8px; }
        p { margin: 0 0 1em; }
        .button { display: inline-block; padding: 12px 24px; background: #2563eb; color: #fff !important; text-decoration: none; border-radius: 6px; margin: 16px 0; }
        .button:hover { background: #1d4ed8; }
        .footer { margin-top: 24px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="content">
            @foreach($introLines as $line)
                <p>{{ $line }}</p>
            @endforeach

            @if(!empty($userLogin))
                <p>Ваш логин: <strong>{{ $userLogin }}</strong></p>
            @endif

            <p>
                <a href="{{ $actionUrl }}" class="button">{{ $actionText }}</a>
            </p>

            @foreach($outroLines as $line)
                <p>{{ str_replace(':count', $expireMinutes ?? 60, $line) }}</p>
            @endforeach
        </div>
    </div>
</body>
</html>
