<?php
require_once 'auth.php';
requireAuth();
requireCsrf();

require_once 'pdo.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit;
}

try {
    $id = isset($_POST['id']) && $_POST['id'] ? (int)$_POST['id'] : null;
    $categoryName = $_POST['categoryName'];

    if ($id) {
        // Update existing category
        $sql = "UPDATE sCategory SET categoryName = :categoryName WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':categoryName', $categoryName, PDO::PARAM_STR);
    } else {
        // Insert new category
        $sql = "INSERT INTO sCategory (categoryName, categoryNo, state) VALUES (:categoryName, 0, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':categoryName', $categoryName, PDO::PARAM_STR);
    }

    $stmt->execute();

    header('Location: admin.php?success=category_saved');
} catch (Exception $e) {
    header('Location: admin.php?error=' . urlencode($e->getMessage()));
}
exit;
