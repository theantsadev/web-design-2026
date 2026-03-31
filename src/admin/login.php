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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/admin/assets/modern-admin.css">
    <link rel="stylesheet" href="/admin/assets/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-background">
            <div class="bg-element bg-element-1"></div>
            <div class="bg-element bg-element-2"></div>
            <div class="bg-element bg-element-3"></div>
        </div>

        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-newspaper"></i>
                </div>
                <h1>Le Monde Iran</h1>
                <p>Administration</p>
            </div>

            <div class="login-form-container">
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo escape($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo escape($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label for="login" class="form-label">
                            <i class="fas fa-user"></i>
                            Identifiant
                        </label>
                        <input type="text" id="login" name="login" class="form-input" required autofocus placeholder="Entrez votre identifiant">
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>
                            Mot de passe
                        </label>
                        <input type="password" id="password" name="password" class="form-input" required placeholder="Entrez votre mot de passe">
                    </div>

                    <button type="submit" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        Se connecter
                    </button>
                </form>
            </div>

            <div class="login-footer">
                <p><i class="fas fa-shield-alt"></i> Connexion sécurisée</p>
            </div>
        </div>

        <div class="back-to-site">
            <a href="/" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i>
                Retour au site
            </a>
        </div>
    </div>
</body>
</html>
