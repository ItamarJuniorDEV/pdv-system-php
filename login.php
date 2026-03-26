<?php
declare(strict_types=1);

define('BASE_PATH', __DIR__);
define('BASE_URL', '');

require_once BASE_PATH . '/config/Config.php';
Config::load();

require_once BASE_PATH . '/config/Auth.php';
Auth::start();

if (Auth::check()) {
    header('Location: /');
    exit;
}

require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/config/LoginRateLimiter.php';
require_once BASE_PATH . '/dao/UserDAO.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['senha'] ?? '';

    if (!Auth::verifyCsrf($_POST['_csrf'] ?? '')) {
        $error = 'Requisição inválida. Recarregue a página e tente novamente.';
    } elseif ($email === '' || $pass === '') {
        $error = 'Preencha e-mail e senha.';
    } else {
        $ip      = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $limiter = new LoginRateLimiter();

        if ($limiter->isBlocked($ip)) {
            $error = 'Muitas tentativas. Aguarde 15 minutos e tente novamente.';
        } else {
            $dao  = new UserDAO();
            $user = $dao->findByEmail($email);

            if ($user && password_verify($pass, $user['senha'])) {
                $limiter->clear($ip);
                Auth::login($user);
                header('Location: /');
                exit;
            }

            $limiter->record($ip);
            $error = 'E-mail ou senha incorretos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso — PDV System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(0,0,0,.09);
            padding: 40px 36px;
            width: 100%;
            max-width: 380px;
        }
        .login-brand {
            width: 48px; height: 48px;
            background: #4361ee;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; color: #fff;
            margin: 0 auto 20px;
        }
        .btn-primary {
            background: #4361ee;
            border-color: #4361ee;
        }
        .btn-primary:hover {
            background: #3451d1;
            border-color: #3451d1;
        }
    </style>
</head>
<body>

<div class="login-card">

    <div class="login-brand">
        <i class="fas fa-cash-register"></i>
    </div>

    <h5 class="text-center fw-bold mb-1">PDV System</h5>
    <p class="text-center text-muted small mb-4">Informe suas credenciais para continuar</p>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger py-2 small">
            <i class="fas fa-circle-exclamation me-1"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/login.php" novalidate>
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars(Auth::generateCsrf()) ?>">
        <div class="mb-3">
            <label class="form-label fw-semibold small" for="email">E-mail</label>
            <input type="email" id="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   placeholder="seu@email.com" autofocus required>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold small" for="senha">Senha</label>
            <input type="password" id="senha" name="senha" class="form-control"
                   placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">
            Entrar
        </button>
    </form>

</div>

</body>
</html>
