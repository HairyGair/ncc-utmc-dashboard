<?php

declare(strict_types=1);

require_once __DIR__ . '/api/config.php';

ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);
ini_set('session.cookie_lifetime', (string) SESSION_LIFETIME);

session_start();

// Already logged in
if (!empty($_SESSION['authenticated'])) {
    header('Location: index.php');
    exit;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$locked = false;

// Brute force check
function get_attempts_file(): string {
    $dir = sys_get_temp_dir() . '/utmc_auth';
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return $dir . '/' . md5($ip) . '.json';
}

function check_lockout(): array {
    $file = get_attempts_file();
    if (!file_exists($file)) {
        return ['attempts' => 0, 'locked' => false];
    }
    $data = json_decode(file_get_contents($file), true) ?: ['attempts' => 0, 'last' => 0];
    $elapsed = time() - ($data['last'] ?? 0);
    if ($data['attempts'] >= MAX_LOGIN_ATTEMPTS && $elapsed < (LOCKOUT_MINUTES * 60)) {
        $remaining = ceil((LOCKOUT_MINUTES * 60 - $elapsed) / 60);
        return ['attempts' => $data['attempts'], 'locked' => true, 'remaining' => $remaining];
    }
    if ($elapsed >= (LOCKOUT_MINUTES * 60)) {
        @unlink($file);
        return ['attempts' => 0, 'locked' => false];
    }
    return ['attempts' => $data['attempts'], 'locked' => false];
}

function record_attempt(): void {
    $file = get_attempts_file();
    $data = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];
    $data['attempts'] = ($data['attempts'] ?? 0) + 1;
    $data['last'] = time();
    file_put_contents($file, json_encode($data), LOCK_EX);
}

function clear_attempts(): void {
    @unlink(get_attempts_file());
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lockout = check_lockout();

    if ($lockout['locked']) {
        $error = "Too many attempts. Try again in {$lockout['remaining']} minutes.";
        $locked = true;
    } else {
        // Verify CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            $error = 'Invalid request. Please try again.';
        } else {
            $password = $_POST['password'] ?? '';
            // Constant-time comparison via password_verify
            if (password_verify($password, AUTH_HASH)) {
                clear_attempts();
                session_regenerate_id(true);
                $_SESSION['authenticated'] = true;
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                $return = $_POST['return'] ?? 'index.php';
                // Only allow relative URLs
                if (str_starts_with($return, '/') || str_starts_with($return, 'http')) {
                    $return = 'index.php';
                }
                header('Location: ' . $return);
                exit;
            } else {
                record_attempt();
                $lockout = check_lockout();
                if ($lockout['locked']) {
                    $error = "Too many attempts. Try again in {$lockout['remaining']} minutes.";
                    $locked = true;
                } else {
                    $remaining = MAX_LOGIN_ATTEMPTS - $lockout['attempts'];
                    $error = "Incorrect password. {$remaining} attempts remaining.";
                }
            }
        }
    }
    // Regenerate CSRF token after each POST
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$expired = isset($_GET['expired']);
$returnUrl = $_GET['return'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="apple-mobile-web-app-capable" content="yes">
<title>UTMC Dashboard Login</title>
<style>
  :root {
    --navy: #1B2A4A;
    --navy-dark: #111E35;
    --navy-light: #2A3F6B;
    --accent: #2E7DCC;
    --bg: #F0F2F5;
    --white: #FFFFFF;
    --border: #DDE1E9;
    --text: #1A1A2E;
    --text-mid: #4A5568;
    --red: #B91C1C;
    --red-bg: #FEF2F2;
    --amber: #92400E;
    --amber-bg: #FFFBEB;
    --radius: 12px;
    --shadow: 0 2px 8px rgba(0,0,0,0.08);
    --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
  }
  .login-card {
    background: var(--white);
    border-radius: var(--radius);
    box-shadow: var(--shadow-md);
    padding: 40px 32px;
    width: 100%;
    max-width: 400px;
  }
  .login-header {
    background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 50%, var(--navy-light) 100%);
    color: white;
    padding: 24px;
    border-radius: var(--radius) var(--radius) 0 0;
    margin: -40px -32px 32px;
    text-align: center;
  }
  .login-header h1 { font-size: 1.25rem; font-weight: 700; margin-bottom: 4px; }
  .login-header p { font-size: 0.8rem; opacity: 0.75; text-transform: uppercase; letter-spacing: 0.03em; }
  .form-group { margin-bottom: 20px; }
  label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-mid);
    margin-bottom: 6px;
  }
  input[type="password"] {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border);
    border-radius: 8px;
    font-size: 1rem;
    outline: none;
    transition: border-color 0.2s;
  }
  input[type="password"]:focus {
    border-color: var(--accent);
  }
  input.error {
    border-color: var(--red);
  }
  .btn {
    width: 100%;
    padding: 14px;
    background: var(--accent);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
  }
  .btn:hover { opacity: 0.9; }
  .btn:disabled { opacity: 0.5; cursor: not-allowed; }
  .alert {
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 0.85rem;
    margin-bottom: 16px;
  }
  .alert-error { background: var(--red-bg); color: var(--red); border: 1px solid #FECACA; }
  .alert-warn { background: var(--amber-bg); color: var(--amber); border: 1px solid #FDE68A; }
</style>
</head>
<body>
  <form class="login-card" method="POST" action="login.php">
    <div class="login-header">
      <h1>UTMC Dashboards</h1>
      <p>Newcastle City Council</p>
    </div>

    <?php if ($expired): ?>
      <div class="alert alert-warn">Session expired. Please log in again.</div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="hidden" name="return" value="<?= htmlspecialchars($returnUrl) ?>">

    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" autocomplete="current-password"
             class="<?= $error ? 'error' : '' ?>" <?= $locked ? 'disabled' : 'autofocus' ?> required>
    </div>

    <button type="submit" class="btn" <?= $locked ? 'disabled' : '' ?>>Log In</button>
  </form>
</body>
</html>
