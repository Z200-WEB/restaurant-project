<?php
require_once 'pdo.php';
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    $orderId = (int)$_POST['orderId'];
    $change = (int)$_POST['change']; // +1 or -1
    
    // Get current amount
    $sql = "SELECT amount FROM sOrder WHERE id = :orderId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':orderId', $orderId, PDO::PARAM_INT);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    $newAmount = $order['amount'] + $change;
    
    if ($newAmount <= 0) {
        // Delete if quantity becomes 0 or less
        $sql = "DELETE FROM sOrder WHERE id = :orderId";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':orderId', $orderId, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // Update quantity
        $sql = "UPDATE sOrder SET amount = :amount WHERE id = :orderId";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':amount', $newAmount, PDO::PARAM_INT);
        $stmt->bindValue(':orderId', $orderId, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>