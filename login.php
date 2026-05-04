<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect to the appropriate page
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: Admin/home.php');
    } else {
        header('Location: home.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role']      = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: Admin/home.php');
                } else {
                    header('Location: home.php');
                }
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In – AuraCommerce</title>
    <meta name="description" content="Sign in to your AuraCommerce account to start shopping.">
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

        /* Brand */
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

        /* Card */
        .auth-card {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 2rem 2rem;
            background: #fff;
        }

        /* Error alert */
        .alert-error {
            background: #fdf1ec;
            border: 1px solid #e8c4b0;
            color: #8B2E01;
            border-radius: 6px;
            padding: 0.65rem 0.9rem;
            font-size: 0.875rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Form */
        .form-group { margin-bottom: 1.1rem; }

        .form-row-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.45rem;
        }

        label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }

        .forgot-link {
            font-size: 0.8rem;
            color: #0047AB;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.15s;
        }
        .forgot-link:hover { color: #003a8c; }

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

        /* Password wrapper */
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

        /* Submit button */
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

        /* Footer text */
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
    <p class="brand-sub">Welcome back. Please enter your details.</p>

    <div class="auth-card">

        <?php if ($error): ?>
            <div class="alert-error">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate>

            <div class="form-group">
                <label for="login-email">Email</label>
                <input
                    type="email"
                    id="login-email"
                    name="email"
                    placeholder="name@company.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="form-group">
                <div class="form-row-label">
                    <label for="login-password">Password</label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>
                <div class="password-wrapper">
                    <input
                        type="password"
                        id="login-password"
                        name="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="toggle-pw" onclick="togglePassword('login-password', this)" aria-label="Toggle password visibility">
                        <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="signin-btn">Sign In</button>
        </form>
    </div>

    <p class="auth-footer">
        Don't have an account? <a href="register.php">Sign up</a>
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
</script>
</body>
</html>
