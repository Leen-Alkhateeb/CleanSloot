<?php
require_once __DIR__ . '/includes/functions.php';

// One page for everyone: customers and admins log in here, new customers register here.
if ($user) {
    redirect(url($user['role'] === 'admin' ? 'admin/index.php' : 'index.php'));
}

// ?tab=register shows "Register", anything else shows "Login"
$tab = ($_POST['tab'] ?? $_GET['tab'] ?? '') === 'register' ? 'register' : 'login';

// Where to go after login. Only paths inside this site are accepted.
$next = $_POST['next'] ?? $_GET['next'] ?? '';
if (!preg_match('#^/(?!/)[^\\\\]*$#', $next)) {
    $next = '';
}

$error  = '';
$errors = [];
$data   = ['name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $users = new User($db);

    if ($tab === 'login' && ($wait = login_locked())) {
        $error = "Too many attempts. Try again in $wait minute(s).";
    } elseif ($tab === 'login') {
        $found = $users->login(trim($_POST['email'] ?? ''), $_POST['password'] ?? '');

        if ($found) {
            login_ok();
            login_user($found);
            // Admins always land on the dashboard
            redirect($found['role'] === 'admin' ? url('admin/index.php') : ($next ?: url('index.php')));
        }
        login_failed();
        $error = 'Wrong email or password.';
    } else {
        $data = [
            'name'  => trim($_POST['name'] ?? ''),
            'email' => strtolower(trim($_POST['email'] ?? '')),
            'phone' => trim($_POST['phone'] ?? ''),
        ];
        $password = $_POST['password'] ?? '';

        if (mb_strlen($data['name']) < 2 || mb_strlen($data['name']) > 100) {
            $errors['name'] = 'Name must be between 2 and 100 characters.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email.';
        } elseif ($users->emailExists($data['email'])) {
            $errors['email'] = 'This email is already registered.';
        }
        if ($data['phone'] !== '' && !preg_match('/^[0-9+\s-]{7,20}$/', $data['phone'])) {
            $errors['phone'] = 'Enter a valid phone number.';
        }
        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }
        if ($password !== ($_POST['password_confirmation'] ?? '')) {
            $errors['password_confirmation'] = 'Passwords do not match.';
        }

        if (!$errors) {
            $users->register($data['name'], $data['email'], $data['phone'], $password);
            flash('Account created. You can log in now.');
            redirect(url('login.php' . ($next ? '?next=' . urlencode($next) : '')));
        }
    }
}

$title  = ($tab === 'login' ? 'Login' : 'Register') . ' | CleanSlot';
$active = '';
require __DIR__ . '/includes/header.php';
?>

<main id="main" class="section">
    <div class="container">
        <div class="auth">
            <div class="auth-art" style="background-image: url('<?= e(photo('machines', 900)) ?>')">
                <h2>Welcome to CleanSlot</h2>
                <p>One account to order services, shop, pick your pickup time and track every order.</p>
            </div>

            <div class="auth-form stack">
                <div class="chips">
                    <a href="<?= url('login.php') ?>" class="chip <?= $tab === 'login' ? 'active' : '' ?>">Login</a>
                    <a href="<?= url('login.php?tab=register') ?>" class="chip <?= $tab === 'register' ? 'active' : '' ?>">Register</a>
                </div>

                <?php if ($tab === 'login'): ?>
                    <form method="post" class="stack">
                        <?= csrf_field() ?>
                        <input type="hidden" name="tab" value="login">
                        <input type="hidden" name="next" value="<?= e($next) ?>">

                        <?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>

                        <label class="field"><span>Email</span>
                            <input type="email" name="email" required autofocus>
                        </label>
                        <label class="field"><span>Password</span>
                            <input type="password" name="password" required>
                        </label>

                        <button class="btn btn-primary btn-block" type="submit">Login</button>
                        <p class="muted">Admin? Log in here too. You will be taken to the dashboard.</p>
                    </form>
                <?php else: ?>
                    <form method="post" class="stack">
                        <?= csrf_field() ?>
                        <input type="hidden" name="tab" value="register">
                        <input type="hidden" name="next" value="<?= e($next) ?>">

                        <label class="field"><span>Full name</span>
                            <input name="name" value="<?= e($data['name']) ?>" required>
                            <?php if (isset($errors['name'])): ?><small class="field-error"><?= e($errors['name']) ?></small><?php endif; ?>
                        </label>

                        <label class="field"><span>Email</span>
                            <input type="email" name="email" value="<?= e($data['email']) ?>" required>
                            <?php if (isset($errors['email'])): ?><small class="field-error"><?= e($errors['email']) ?></small><?php endif; ?>
                        </label>

                        <label class="field"><span>Phone (optional)</span>
                            <input type="tel" name="phone" value="<?= e($data['phone']) ?>">
                            <?php if (isset($errors['phone'])): ?><small class="field-error"><?= e($errors['phone']) ?></small><?php endif; ?>
                        </label>

                        <div class="two">
                            <label class="field"><span>Password</span>
                                <input type="password" name="password" minlength="8" required>
                                <?php if (isset($errors['password'])): ?><small class="field-error"><?= e($errors['password']) ?></small><?php endif; ?>
                            </label>
                            <label class="field"><span>Confirm password</span>
                                <input type="password" name="password_confirmation" minlength="8" required>
                                <?php if (isset($errors['password_confirmation'])): ?><small class="field-error"><?= e($errors['password_confirmation']) ?></small><?php endif; ?>
                            </label>
                        </div>

                        <button class="btn btn-primary btn-block" type="submit">Create account</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
