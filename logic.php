<?php
require_once 'pdo.php';
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

try {
    $itemId = (int)$_POST['itemId'];
    $tableNo = (int)$_POST['tableNo'];
    $amount = (int)$_POST['amount'];

    if ($itemId <= 0 || $tableNo <= 0 || $amount <= 0) {
        throw new Exception('Invalid parameters');
    }

    // Check if there's an existing draft order (state=0) for this table
    $sql = "SELECT orderNo FROM sManagement WHERE tableNo = :tableNo AND state = 0";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':tableNo', $tableNo, PDO::PARAM_INT);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Use existing draft order
        $orderNo = $existing['orderNo'];
    } else {
        // Create new draft order with generated orderNo
        $orderNo = date('YmdHis') . '-' . sprintf('%04d', rand(0, 9999));

        $sql = "INSERT INTO sManagement (state, orderNo, tableNo) VALUES (0, :orderNo, :tableNo)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':orderNo', $orderNo, PDO::PARAM_STR);
        $stmt->bindValue(':tableNo', $tableNo, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Check if this item already exists in the order
    $sql = "SELECT id, amount FROM sOrder WHERE orderNo = :orderNo AND itemNo = :itemNo";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':orderNo', $orderNo, PDO::PARAM_STR);
    $stmt->bindValue(':itemNo', $itemId, PDO::PARAM_INT);
    $stmt->execute();
    $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingItem) {
        // Update quantity of existing item
        $newAmount = $existingItem['amount'] + $amount;
        $sql = "UPDATE sOrder SET amount = :amount WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':amount', $newAmount, PDO::PARAM_INT);
        $stmt->bindValue(':id', $existingItem['id'], PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // Add new item to order
        $sql = "INSERT INTO sOrder (state, orderNo, itemNo, amount) VALUES (1, :orderNo, :itemNo, :amount)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':orderNo', $orderNo, PDO::PARAM_STR);
        $stmt->bindValue(':itemNo', $itemId, PDO::PARAM_INT);
        $stmt->bindValue(':amount', $amount, PDO::PARAM_INT);
        $stmt->execute();
    }

    echo json_encode(['status' => 'success', 'orderNo' => $orderNo]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
