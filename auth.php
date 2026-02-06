<?php
/**
 * Authentication & CSRF Protection System
 * SmartOrder - Restaurant Management System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin credentials - FIXED: Use pre-hashed password
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require authentication - redirect to login if not authenticated
 */
function requireAuth(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Login user
 */
function login(string $username, string $password): bool {
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['login_time'] = time();
        regenerateCsrfToken();
        return true;
    }
    return false;
}

/**
 * Logout user
 */
function logout(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Generate CSRF token
 */
function generateCsrfToken(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Regenerate CSRF token
 */
function regenerateCsrfToken(): string {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCsrfToken(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Require valid CSRF token for POST/DELETE requests
 */
function requireCsrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!validateCsrfToken($token)) {
            http_response_code(403);
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            } else {
                header('Location: admin.php?error=' . urlencode('Security token expired. Please try again.'));
            }
            exit;
        }
    }
}

/**
 * Get CSRF token input field for forms
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '">';
}

/**
 * Get current admin username
 */
function getAdminUsername(): string {
    return $_SESSION['admin_username'] ?? 'Admin';
}
