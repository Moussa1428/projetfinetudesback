<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistant créé</title>
     <style>
        /* Styles de base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        /* En-tête */
        .header {
            background: #073173;
            padding: 25px 30px;
            text-align: center;
            color: white;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin: 0;
        }

        /* Contenu principal */
        .content {
            padding: 35px 30px;
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 25px;
            color: #2978FD;
            font-size: 18px;
        }

        .image-auth {
            text-align: center;
            margin: 25px 0;
        }

        .image-auth img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #2978FD;
            box-shadow: 0 4px 10px rgba(41, 120, 253, 0.3);
        }

        .message {
            font-size: 16px;
            margin-bottom: 25px;
            color: #444;
            text-align: center;
        }

        /* Identifiants */
        .credentials {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin: 25px 0;
            border-left: 4px solid #2978FD;
        }

        .credential-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #e0e0e0;
        }

        .credential-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .credential-label {
            font-weight: 600;
            color: #073173;
        }

        .credential-value {
            font-weight: 700;
            color: #2978FD;
            font-family: 'Courier New', monospace;
        }

        /* Bouton */
        .btn-container {
            text-align: center;
            margin: 30px 0 25px;
        }

        .btn {
            display: inline-block;
            padding: 14px 35px;
            background: #073173;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(7, 49, 115, 0.25);
        }

        .btn:hover {
            background: #06285e;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(7, 49, 115, 0.35);
        }

        /* Sécurité */
        .security-note {
            background: #fff9e6;
            padding: 16px 20px;
            border-radius: 10px;
            margin: 25px 0;
            border-left: 4px solid #ffc107;
            font-size: 14px;
            color: #856404;
        }

        .security-note strong {
            color: #073173;
        }

        /* Pied de page */
        .footer {
            background: #073173;
            padding: 20px 30px;
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }

        .footer p {
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .container {
                border-radius: 0;
                margin: 0;
            }

            .content {
                padding: 25px 20px;
            }

            .credential-item {
                flex-direction: column;
            }

            .credential-value {
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenue Assistant !</h1>
        </div>

        <div class="content">
            <div class="welcome-text">
                <p>Votre compte a été créé avec succès</p>
            </div>

            <div class="image-auth">
                <img src="https://tse3.mm.bing.net/th/id/OIP.8glRmF1NLH3HgvuEVoZmDQHaHa?rs=1&pid=ImgDetMain" alt="Authentication Image">
            </div>

            <p class="message">Voici vos identifiants de connexion :</p>

            <div class="credentials">
                <div class="credential-item">
                    <span class="credential-label">Email :</span>
                    <span class="credential-value">{{ $email }}</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Mot de passe :</span>
                    <span class="credential-value">{{ $password }}</span>
                </div>
            </div>

            <div class="btn-container">
                <a href="{{ url('/') }}" class="btn">Se connecter</a>
            </div>

            <div class="security-note">
                <strong>Recommandation de sécurité :</strong> Pour protéger votre compte, veuillez changer votre mot de passe après votre première connexion.
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} ISINotify. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
