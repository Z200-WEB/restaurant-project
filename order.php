<?php
// UTF-8 ENCODING - MUST BE FIRST!
header('Content-Type: text/html; charset=UTF-8');

// Authentication required - order details should only be visible to staff
require_once 'auth.php';
requireAuth();

// Load database connection
require_once 'pdo.php';

$orderNo = isset($_GET['orderNo']) ? $_GET['orderNo'] : '';

if (!$orderNo) {
    echo "注文番号が指定されていません。";
    exit;
}

$sqlMgmt = "SELECT * FROM sManagement WHERE orderNo = :orderNo";
$stmtMgmt = $pdo->prepare($sqlMgmt);
$stmtMgmt->bindValue(':orderNo', $orderNo, PDO::PARAM_STR);
$stmtMgmt->execute();
$mgmt = $stmtMgmt->fetch(PDO::FETCH_ASSOC);

if (!$mgmt) {
    echo "注文が見つかりません。";
    exit;
}

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
    <title>&#x1F9FE; 注文詳細 - <?php echo htmlspecialchars($orderNo); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Helvetica Neue", Arial, "Hiragino Kaku Gothic ProN", sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .receipt-wrapper {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            position: relative;
        }
        
        .receipt-header {
            text-align: center;
            border-bottom: 3px dashed #ddd;
            padding-bottom: 30px;
            margin-bottom: 30px;
        }
        
        .receipt-title {
            font-size: 2.5em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .receipt-subtitle {
            color: #7f8c8d;
            font-size: 1.1em;
        }
        
        .order-info-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .info-label {
            font-size: 0.85em;
            opacity: 0.9;
        }
        
        .info-value {
            font-size: 1.2em;
            font-weight: 700;
        }
        
        .items-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 1.3em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .items-table thead {
            background: #f8f9fa;
        }
        
        .items-table th {
            padding: 15px 10px;
            text-align: left;
            font-weight: 700;
            color: #2c3e50;
            border-bottom: 2px solid #ddd;
        }
        
        .items-table td {
            padding: 15px 10px;
            border-bottom: 1px solid #eee;
        }
        
        .items-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .item-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .item-price,
        .item-qty,
        .item-subtotal {
            color: #7f8c8d;
            text-align: right;
        }
        
        .item-subtotal {
            font-weight: 700;
            color: #FF6B35;
        }
        
        .total-section {
            border-top: 3px dashed #ddd;
            padding-top: 25px;
            margin-top: 25px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 1.1em;
        }
        
        .total-row.grand-total {
            background: linear-gradient(135deg, #FF6B35 0%, #FF8C42 100%);
            color: white;
            padding: 20px 25px;
            border-radius: 15px;
            font-size: 1.5em;
            font-weight: 700;
            margin-top: 15px;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 40px;
        }
        
        .btn {
            flex: 1;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 1.1em;
            font-weight: 700;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-back {
            background: #95a5a6;
            color: white;
        }
        
        .btn-back:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }
        
        .btn-print {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }
        
        .receipt-footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 3px dashed #ddd;
            color: #7f8c8d;
            font-size: 0.95em;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .actions {
                display: none;
            }
            
            .receipt-wrapper {
                box-shadow: none;
            }
        }
        
        @media (max-width: 768px) {
            .order-info-grid {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .items-table th:nth-child(2),
            .items-table td:nth-child(2) {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="receipt-wrapper">
        <div class="receipt-header">
            <div class="receipt-title">&#x1F9FE; 注文詳細</div>
            <div class="receipt-subtitle">Order Receipt</div>
        </div>
        
        <div class="order-info-box">
            <div class="order-info-grid">
                <div class="info-item">
                    <div class="info-label">&#x1F4CB; 注文番号</div>
                    <div class="info-value"><?php echo htmlspecialchars(substr($mgmt['orderNo'], -12)); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">&#x1F37D;&#xFE0F; テーブル番号</div>
                    <div class="info-value">Table <?php echo htmlspecialchars($mgmt['tableNo']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">&#x1F4C5; 注文日時</div>
                    <div class="info-value"><?php echo date('Y/m/d H:i', strtotime($mgmt['dateA'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">&#x1F504; 最終更新</div>
                    <div class="info-value"><?php echo date('Y/m/d H:i', strtotime($mgmt['dateB'])); ?></div>
                </div>
            </div>
        </div>

        <div class="items-section">
            <div class="section-title">
                <span>&#x1F4DD;</span>
                <span>注文商品</span>
            </div>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>商品名</th>
                        <th style="text-align: right;">単価</th>
                        <th style="text-align: right;">数量</th>
                        <th style="text-align: right;">小計</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($details as $detail): ?>
                    <tr>
                        <td class="item-name"><?php echo htmlspecialchars($detail['name']); ?></td>
                        <td class="item-price">&yen;<?php echo number_format($detail['price']); ?></td>
                        <td class="item-qty">&times; <?php echo htmlspecialchars($detail['amount']); ?></td>
                        <td class="item-subtotal">&yen;<?php echo number_format($detail['price'] * $detail['amount']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="total-section">
            <div class="total-row">
                <span>商品点数:</span>
                <span><?php echo count($details); ?>点</span>
            </div>
            
            <div class="total-row grand-total">
                <span>&#x1F4B0; 合計金額</span>
                <span>&yen;<?php echo number_format($total); ?></span>
            </div>
        </div>

        <div class="actions">
            <a href="management.php" class="btn btn-back">&larr; 一覧に戻る</a>
            <button onclick="window.print()" class="btn btn-print">&#x1F5A8;&#xFE0F; 印刷する</button>
        </div>

        <div class="receipt-footer">
            <p>&#x2728; ご注文ありがとうございました &#x2728;</p>
        </div>
    </div>
</div>

</body>
</html>