<?php
use App\Controllers\AuthController;
use App\Middlewares\Auth;
use App\Middlewares\Csrf;
use function App\Config\e;

if (Auth::checkAdmin()) {
    header('Location: /admin');
    exit;
}

$errorLogin = AuthController::handleLogin();
?>

<section class="section">
    <div class="container">
        <div class="card" style="max-width: 28rem; margin-left: auto; margin-right: auto; padding: var(--space-4);">
            <div style="text-align: center; margin-bottom: var(--space-3);">
                <span class="brand-mark" style="margin-bottom: var(--space-2);">VM</span>
                <h1 style="font-size: var(--text-xl);">Acceso Administrativo</h1>
                <p style="font-size: var(--text-sm); color: var(--color-text-muted); margin-bottom: 0;">
                    Uso exclusivo para personal médico de Veterinaria Madri
                </p>
            </div>

            <?php if ($errorLogin !== null): ?>
                <div class="notice-box" role="alert" style="border-left-color: var(--color-status-danger); background-color: var(--color-primary-soft); margin-bottom: var(--space-3);">
                    <strong style="color: var(--color-primary);">Acceso denegado:</strong> <?= e($errorLogin) ?>
                </div>
            <?php endif; ?>

            <form action="/admin/login" method="POST" autocomplete="off">
                <?= Csrf::field() ?>

                <div class="form-group">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="admin@veterinariamadri.mx"
                        value="<?= e((string) ($_POST['email'] ?? '')) ?>"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="••••••••••••"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: var(--space-1);">
                    Iniciar Sesión Segura
                </button>
            </form>

            <div style="margin-top: var(--space-3); padding-top: var(--space-2); border-top: 1px solid var(--color-border); text-align: center; font-size: var(--text-xs); color: var(--color-text-muted);">
                Sesión protegida mediante cifrado Argon2id, token anti-CSRF y bloqueo contra fuerza bruta.
            </div>
        </div>
    </div>
</section>