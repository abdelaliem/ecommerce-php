<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect
if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'Admin/home.php' : 'home.php'));
    exit();
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name            = trim($_POST['name'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name)) {
        $errors[] = 'Full name is required.';
    } elseif (strlen($name) < 2) {
        $errors[] = 'Name must be at least 2 characters.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = 'An account with this email already exists.';
        } else {
            // Hash password and insert user
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt->close();
            $insert = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            $insert->bind_param('sss', $name, $email, $hashed);

            if ($insert->execute()) {
                $insert->close();
                // Auto-login the new user
                session_regenerate_id(true);
                $newId = $conn->insert_id;
                // Fetch inserted user
                $sel = $conn->prepare("SELECT id, name, role FROM users WHERE id = ?");
                $sel->bind_param('i', $newId);
                $sel->execute();
                $user = $sel->get_result()->fetch_assoc();
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role']      = $user['role'];
                $sel->close();
                header('Location: home.php');
                exit();
            } else {
                $errors[] = 'Registration failed. Please try again.';
                $insert->close();
            }
        }
        if (!empty($errors)) $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account – AuraCommerce</title>
    <meta name="description" content="Create a new AuraCommerce account and start shopping today.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8F9FA;
            color: #374151;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .brand-name {
            font-size: 1.85rem;
            font-weight: 700;
            color: #0047AB;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .brand-sub {
            font-size: 0.92rem;
            color: #6C757D;
            margin-bottom: 2rem;
        }

        .auth-card {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 2rem 2rem;
            background: #fff;
        }

        /* Error list */
        .alert-error {
            background: #fdf1ec;
            border: 1px solid #e8c4b0;
            color: #8B2E01;
            border-radius: 6px;
            padding: 0.65rem 0.9rem;
            font-size: 0.875rem;
            margin-bottom: 1.25rem;
        }
        .alert-error ul { margin: 0; padding-left: 1.2rem; }
        .alert-error li { margin-top: 0.2rem; }
        .alert-error li:first-child { margin-top: 0; }

        .form-group { margin-bottom: 1.1rem; }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.45rem;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 0.6rem 0.85rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            color: #111827;
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        input::placeholder { color: #9ca3af; }
        input:focus {
            border-color: #0047AB;
            box-shadow: 0 0 0 3px rgba(0, 71, 171, 0.12);
        }

        .password-wrapper { position: relative; }
        .password-wrapper input { padding-right: 2.5rem; }
        .toggle-pw {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            padding: 0;
            font-size: 1rem;
            line-height: 1;
            transition: color 0.15s;
        }
        .toggle-pw:hover { color: #374151; }

        /* Password strength bar */
        .pw-strength-bar {
            height: 4px;
            border-radius: 2px;
            background: #e5e7eb;
            margin-top: 0.5rem;
            overflow: hidden;
        }
        .pw-strength-fill {
            height: 100%;
            border-radius: 2px;
            width: 0%;
            transition: width 0.3s, background 0.3s;
        }
        .pw-strength-label {
            font-size: 0.72rem;
            color: #9ca3af;
            margin-top: 0.25rem;
        }

        .btn-submit {
            width: 100%;
            padding: 0.72rem;
            background: #0047AB;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            margin-top: 0.5rem;
            letter-spacing: 0.01em;
        }
        .btn-submit:hover { background: #003a8c; }
        .btn-submit:active { transform: scale(0.98); }

        /* Terms */
        .terms-note {
            font-size: 0.78rem;
            color: #9ca3af;
            text-align: center;
            margin-top: 1rem;
            line-height: 1.5;
        }
        .terms-note a { color: #0047AB; text-decoration: none; }
        .terms-note a:hover { text-decoration: underline; }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.875rem;
            color: #6C757D;
        }
        .auth-footer a {
            color: #0047AB;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.15s;
        }
        .auth-footer a:hover { color: #003a8c; }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <h1 class="brand-name">AuraCommerce</h1>
    <p class="brand-sub">Create your account. It's free.</p>

    <div class="auth-card">

        <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <?php if (count($errors) === 1): ?>
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:inline;vertical-align:-2px;margin-right:4px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <?= htmlspecialchars($errors[0]) ?>
                <?php else: ?>
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" novalidate>

            <div class="form-group">
                <label for="reg-name">Full Name</label>
                <input
                    type="text"
                    id="reg-name"
                    name="name"
                    placeholder="John Doe"
                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                    autocomplete="name"
                    required
                >
            </div>

            <div class="form-group">
                <label for="reg-email">Email</label>
                <input
                    type="email"
                    id="reg-email"
                    name="email"
                    placeholder="name@company.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="form-group">
                <label for="reg-password">Password</label>
                <div class="password-wrapper">
                    <input
                        type="password"
                        id="reg-password"
                        name="password"
                        placeholder="Min. 6 characters"
                        autocomplete="new-password"
                        oninput="checkStrength(this.value)"
                        required
                    >
                    <button type="button" class="toggle-pw" onclick="togglePassword('reg-password', this)" aria-label="Toggle password visibility">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <div class="pw-strength-bar"><div class="pw-strength-fill" id="pw-bar"></div></div>
                <div class="pw-strength-label" id="pw-label"></div>
            </div>

            <div class="form-group">
                <label for="reg-confirm-password">Confirm Password</label>
                <div class="password-wrapper">
                    <input
                        type="password"
                        id="reg-confirm-password"
                        name="confirm_password"
                        placeholder="••••••••"
                        autocomplete="new-password"
                        required
                    >
                    <button type="button" class="toggle-pw" onclick="togglePassword('reg-confirm-password', this)" aria-label="Toggle confirm password visibility">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="signup-btn">Create Account</button>

            <p class="terms-note">
                By signing up, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
            </p>
        </form>
    </div>

    <p class="auth-footer">
        Already have an account? <a href="login.php">Sign in</a>
    </p>
</div>

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    btn.innerHTML = isHidden
        ? `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`
        : `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
}

function checkStrength(val) {
    const bar = document.getElementById('pw-bar');
    const label = document.getElementById('pw-label');
    if (!val) { bar.style.width = '0%'; label.textContent = ''; return; }

    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { w: '20%', c: '#ef4444', t: 'Very weak' },
        { w: '40%', c: '#f97316', t: 'Weak' },
        { w: '60%', c: '#eab308', t: 'Fair' },
        { w: '80%', c: '#3b82f6', t: 'Good' },
        { w: '100%', c: '#22c55e', t: 'Strong' },
    ];
    const lvl = levels[Math.min(score - 1, 4)] || levels[0];
    bar.style.width = lvl.w;
    bar.style.background = lvl.c;
    label.textContent = lvl.t;
    label.style.color = lvl.c;
}
</script>
</body>
</html>
