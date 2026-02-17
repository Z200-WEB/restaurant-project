<?php
/**
 * Delete category - requires POST method with CSRF token
 * Security: Authentication + CSRF validation + POST-only
 */
ob_start();

// Authentication first
require_once 'auth.php';
requireAuth();
requireCsrf();

// Load database
require_once 'pdo.php';

// Only allow POST requests (GET-based deletion is a security risk)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php?error=' . urlencode('Invalid request method'));
    exit;
}

// Get ID parameter from POST body
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header('Location: admin.php?error=' . urlencode('Invalid ID'));
    exit;
}

$id = (int)$_POST['id'];

try {
    // Delete category from database
    $sql = "DELETE FROM sCategory WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Clear output buffer and redirect
    ob_end_clean();
    header('Location: admin.php?success=category_deleted&t=' . time());
    exit;

} catch (Exception $e) {
    ob_end_clean();
    header('Location: admin.php?error=' . urlencode($e->getMessage()));
    exit;
}