<?php
require_once 'pdo.php';

$orderNo = isset($_GET['orderNo']) ? $_GET['orderNo'] : '';

if (!$orderNo) {
    echo "注文番号が指定されていません。";
    exit;
}

// 注文ヘッダー情報取得
$sqlMgmt = "SELECT * FROM sManagement WHERE orderNo = :orderNo";
$stmtMgmt = $pdo->prepare($sqlMgmt);
$stmtMgmt->bindValue(':orderNo', $orderNo, PDO::PARAM_STR);
$stmtMgmt->execute();
$mgmt = $stmtMgmt->fetch(PDO::FETCH_ASSOC);

if (!$mgmt) {
    echo "注文が見つかりません。";
    exit;
}

// 注文詳細情報取得 (商品名や価格も結合して取得)
$sqlDetail = "
    SELECT o.*, i.name, i.price
    FROM sOrder o
    JOIN sItem i ON o.itemNo = i.id
    WHERE o.orderNo = :orderNo
";
$stmtDetail = $pdo->prepare($sqlDetail);
$stmtDetail->bindValue(':orderNo', $orderNo, PDO::PARAM_STR);
$stmtDetail->execute();
$details = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);

// 合計金額計算
$total = 0;
foreach ($details as $d) {
    $total += $d['price'] * $d['amount'];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文詳細 - <?php echo htmlspecialchars($orderNo); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>注文詳細</h1>
    
    <div style="margin-bottom: 20px;">
        <p><strong>注文番号:</strong> <?php echo htmlspecialchars($mgmt['orderNo']); ?></p>
        <p><strong>テーブル番号:</strong> Table <?php echo htmlspecialchars($mgmt['tableNo']); ?></p>
        <p><strong>注文日時:</strong> <?php echo htmlspecialchars($mgmt['dateA']); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>商品名</th>
                <th>単価</th>
                <th>数量</th>
                <th>小計</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($details as $detail): ?>
            <tr>
                <td><?php echo htmlspecialchars($detail['name']); ?></td>
                <td>¥<?php echo number_format($detail['price']); ?></td>
                <td><?php echo htmlspecialchars($detail['amount']); ?></td>
                <td>¥<?php echo number_format($detail['price'] * $detail['amount']); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3" style="text-align: right; font-weight: bold;">合計</td>
                <td style="font-weight: bold; color: #e74c3c;">¥<?php echo number_format($total); ?></td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        <a href="management.php" class="btn">一覧に戻る</a>
    </div>
</div>

</body>
</html>
