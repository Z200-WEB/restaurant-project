<?php
require_once 'pdo.php';
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    $orderId = (int)$_POST['orderId'];
    
    // 注文削除
    $sql = "DELETE FROM sOrder WHERE id = :orderId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':orderId', $orderId, PDO::PARAM_INT);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>