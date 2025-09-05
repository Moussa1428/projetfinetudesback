<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notification</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #073173;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
        }
        .body {
            padding: 25px;
            color: #333333;
            font-size: 16px;
            line-height: 1.5;
        }
        .message-box {
            background-color: #f9f9f9;
            padding: 15px;
            border-left: 4px solid #073173;
            border-radius: 4px;
            margin: 15px 0;
        }
        .btn {
            display: inline-block;
            background-color: #073173;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 15px;
            font-weight: bold;
        }
        .footer {
            background-color: #f0f2f5;
            color: #777777;
            padding: 15px;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            Nouvelle Notification
        </div>

        <!-- Body -->
        <div class="body">
            <p>Bonjour,</p>
            <p><strong>{{ $notification->sender->name }}</strong> vous a envoyé une notification :</p>
            <div class="message-box">
                {{ $notification->message }}
            </div>
            <a href="#" class="btn">Voir la notification</a>
            <p style="margin-top:20px;">Merci,<br>L'équipe {{ config('app.name') }}</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.
        </div>
    </div>
</body>
</html>
