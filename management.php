<?php
require_once 'pdo.php';

// 注文一覧取得
// 最新の注文が上に来るように降順で取得
// 合計金額も計算して取得
$sql = "
    SELECT m.*, SUM(i.price * o.amount) as totalAmount
    FROM sManagement m
    LEFT JOIN sOrder o ON m.orderNo = o.orderNo
    LEFT JOIN sItem i ON o.itemNo = i.id
    GROUP BY m.orderNo
    ORDER BY m.dateB DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理画面 - 注文一覧</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>注文管理一覧</h1>
    
    <!-- Dev Links -->
    <div style="margin-bottom: 20px; padding: 10px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">
        <strong>お客様テーブルに置ける表示:</strong>
        <?php for($i=1; $i<=5; $i++): ?>
            <a href="index.php?tableNo=<?php echo $i; ?>" class="btn" style="margin: 0 5px; padding: 5px 10px; font-size: 0.9em;">Table <?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>最終注文日時</th>
                <th>テーブル番号</th>
                <th>注文番号</th>
                <th style="text-align: right;">合計金額</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $week = ['日', '月', '火', '水', '木', '金', '土'];
            foreach ($orders as $order): 
                $ts = strtotime($order['dateB']);
                $dateStr = date('Y年m月d日', $ts) . '（' . $week[date('w', $ts)] . '）<br>' . date('H時i分s秒', $ts);
            ?>
            <tr>
                <td><?php echo $dateStr; ?></td>
                <td>Table <?php echo htmlspecialchars($order['tableNo']); ?></td>
                <td><?php echo htmlspecialchars($order['orderNo']); ?></td>
                <td style="text-align: right;">¥<?php echo number_format($order['totalAmount']); ?></td>
                <td>
                    <a href="order.php?orderNo=<?php echo urlencode($order['orderNo']); ?>" class="btn">詳細を見る</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
