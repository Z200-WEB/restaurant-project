<?php
require_once 'pdo.php';

// JSONレスポンス用ヘッダー
header('Content-Type: application/json; charset=UTF-8');

// POSTメソッドのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// パラメータ取得
$itemId = isset($_POST['itemId']) ? (int)$_POST['itemId'] : 0;
$tableNo = isset($_POST['tableNo']) ? (int)$_POST['tableNo'] : 0;

if ($itemId <= 0 || $tableNo <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    exit;
}

try {
    // トランザクション開始
    $pdo->beginTransaction();

    // 1. 既存の有効な注文(state=1)がこのテーブルにあるか確認
    $sqlCheck = "SELECT orderNo FROM sManagement WHERE tableNo = :tableNo AND state = 1 LIMIT 1";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->bindValue(':tableNo', $tableNo, PDO::PARAM_INT);
    $stmtCheck->execute();
    $existingOrder = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($existingOrder) {
        // 既存の注文がある場合、そのorderNoを使用
        $orderNo = $existingOrder['orderNo'];
        
        // dateB (最終更新日時) を更新
        $sqlUpdate = "UPDATE sManagement SET dateB = CURRENT_TIMESTAMP WHERE orderNo = :orderNo";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->bindValue(':orderNo', $orderNo, PDO::PARAM_STR);
        $stmtUpdate->execute();
        
    } else {
        // 新規注文の場合、orderNoを生成してsManagementにINSERT
        $orderNo = date('YmdHis') . '-' . mt_rand(1000, 9999);
        
        $sqlMgmt = "INSERT INTO sManagement (orderNo, tableNo) VALUES (:orderNo, :tableNo)";
        $stmtMgmt = $pdo->prepare($sqlMgmt);
        $stmtMgmt->bindValue(':orderNo', $orderNo, PDO::PARAM_STR);
        $stmtMgmt->bindValue(':tableNo', $tableNo, PDO::PARAM_INT);
        $stmtMgmt->execute();
    }

    // 2. sOrder (注文明細) にINSERT
    // amountを受け取る (デフォルト1)
    $amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 1;
    if ($amount < 1) $amount = 1;

    $sqlOrder = "INSERT INTO sOrder (orderNo, itemNo, amount) VALUES (:orderNo, :itemNo, :amount)";
    $stmtOrder = $pdo->prepare($sqlOrder);
    $stmtOrder->bindValue(':orderNo', $orderNo, PDO::PARAM_STR);
    $stmtOrder->bindValue(':itemNo', $itemId, PDO::PARAM_INT);
    $stmtOrder->bindValue(':amount', $amount, PDO::PARAM_INT);
    $stmtOrder->execute();

    // コミット
    $pdo->commit();

    echo json_encode(['status' => 'success', 'orderNo' => $orderNo]);

} catch (Exception $e) {
    // ロールバック
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
