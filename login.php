<?php
require_once 'auth.php';

$auth = new Auth();

if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if (isset($_GET['logout'])) {
    $success = 'Vous avez été déconnecté avec succès.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        if ($auth->login($email, $password)) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Ameziane Tours</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Inter", -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            display: flex;
            min-height: 600px;
        }
        
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(1deg); }
        }
        
        .logo-section {
            position: relative;
            z-index: 2;
        }
        
        .logo {
            width: 120px;
            height: 120px;
            margin-bottom: 30px;
            /* filter: brightness(0) invert(1); */
        }
        
        .company-name {
            font-family: "Playfair Display", serif;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 15px;
            letter-spacing: 1px;
        }
        
        .company-tagline {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 300;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .welcome-text {
            font-size: 18px;
            opacity: 0.8;
            line-height: 1.6;
        }
        
        .login-right {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-header {
            margin-bottom: 40px;
        }
        
        .login-title {
            font-family: "Playfair Display", serif;
            font-size: 32px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .login-subtitle {
            color: #7f8c8d;
            font-size: 16px;
            font-weight: 400;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            color: #34495e;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 16px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #ffffff;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            transform: translateY(-1px);
        }
        
        .form-input::placeholder {
            color: #bdc3c7;
        }
        
        .login-button {
            width: 100%;
            padding: 16px 20px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3);
        }
        
        .login-button:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .alert-error {
            background: #fdf2f2;
            color: #e74c3c;
            border: 1px solid #fadbd8;
        }
        
        .alert-success {
            background: #f0f9f0;
            color: #27ae60;
            border: 1px solid #d5f4e6;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 30px;
            color: #95a5a6;
            font-size: 12px;
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 400px;
            }
            
            .login-left {
                padding: 40px 30px;
                min-height: 300px;
            }
            
            .login-right {
                padding: 40px 30px;
            }
            
            .company-name {
                font-size: 28px;
            }
            
            .login-title {
                font-size: 24px;
            }
            
            .logo {
                width: 80px;
                height: 80px;
            }
        }
        
        .loading {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .login-button.loading {
            pointer-events: none;
            opacity: 0.8;
        }
        
        .login-button.loading .loading {
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="logo-section">
                <img src="eagle-logo.png" alt="Ameziane Tours" class="logo">
                <h1 class="company-name">AMEZIANE TOURS</h1>
                <p class="company-tagline">Service Touristique & Transport Personnel</p>
                <p class="welcome-text">
                    Système de gestion des factures<br>
                    Accès sécurisé pour les professionnels
                </p>
            </div>
        </div>
        
        <div class="login-right">
            <div class="login-header">
                <h2 class="login-title">Connexion</h2>
                <p class="login-subtitle">Accédez à votre espace de facturation</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="post" id="loginForm">
                <div class="form-group">
                    <label for="email" class="form-label">Adresse Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="votre@email.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required
                        autocomplete="email"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Mot de Passe</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                </div>
                
                <button type="submit" class="login-button" id="loginBtn">
                    Se Connecter
                    <span class="loading"></span>
                </button>
            </form>


            
            <div class="footer-text">
                © <?= date('Y') ?> Ameziane Tours. Tous droits réservés.<br>
                Système sécurisé de gestion des factures
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.disabled = true;
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            const emailField = document.getElementById('email');
            const passwordField = document.getElementById('password');
            
            if (!emailField.value) {
                emailField.focus();
            } else {
                passwordField.focus();
            }
        });
        
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>
