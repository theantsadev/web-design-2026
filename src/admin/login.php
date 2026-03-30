<?php
/**
 * Page de login - BackOffice
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../session.php';

// Déjà connecté? Rediriger vers le dashboard
if (isLoggedIn()) {
    header('Location: /admin/dashboard/');
    exit;
}

$error = '';
$success = '';

// Traitement du formulaire de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim(postParam('login', ''));
    $password = trim(postParam('password', ''));

    if (empty($login) || empty($password)) {
        $error = 'Login et mot de passe obligatoires';
    } else {
        try {
            // Récupérer l'utilisateur
            $stmt = $pdo->prepare('SELECT id, login, password_hash, role FROM utilisateur WHERE login = ?');
            $stmt->execute([$login]);
            $user = $stmt->fetch();

            if ($user && verifyPassword($password, $user['password_hash'])) {
                // Succès!
                login($user['id'], $user['login'], $user['role']);
                header('Location: /admin/dashboard/');
                exit;
            } else {
                $error = 'Identifiants incorrects';
            }
        } catch (PDOException $e) {
            $error = 'Erreur serveur: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        .login-container h1 {
            text-align: center;
            margin-bottom: 0.5rem;
            color: #333;
            font-size: 1.8rem;
        }

        .login-container p {
            text-align: center;
            color: #888;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .alert {
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>🔐 Admin</h1>
        <p>Connexion au panneau d'administration</p>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo escape($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo escape($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="login">Identifiant</label>
                <input type="text" id="login" name="login" required autofocus placeholder="admin">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required placeholder="admin123">
            </div>

            <button type="submit" class="btn">Connexion</button>
        </form>

        <div class="back-link">
            <a href="/">← Retour au site</a>
        </div>
    </div>
</body>
</html>
