<?php
require_once 'pdo.php';

// テーブル番号取得 (デフォルト1)
$tableNo = isset($_GET['tableNo']) ? (int)$_GET['tableNo'] : 1;

// カテゴリ取得
$sqlCategory = "SELECT * FROM sCategory WHERE state = 1 ORDER BY id ASC";
$stmtCategory = $pdo->prepare($sqlCategory);
$stmtCategory->execute();
$categories = $stmtCategory->fetchAll(PDO::FETCH_ASSOC);

// 商品取得
$sqlItem = "SELECT * FROM sItem WHERE state = 1 ORDER BY id ASC";
$stmtItem = $pdo->prepare($sqlItem);
$stmtItem->execute();
$items = $stmtItem->fetchAll(PDO::FETCH_ASSOC);

// 現在のテーブルの合計金額計算
// sManagementとsOrderを結合して、指定テーブルの未会計(state=1と仮定)の合計を出す
// 今回の要件では「合計金額が表示される」とあるので、単純にそのテーブルの全注文合計を表示する
$sqlTotal = "
    SELECT SUM(i.price * o.amount) as total
    FROM sManagement m
    JOIN sOrder o ON m.orderNo = o.orderNo
    JOIN sItem i ON o.itemNo = i.id
    WHERE m.tableNo = :tableNo
";
$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->bindValue(':tableNo', $tableNo, PDO::PARAM_INT);
$stmtTotal->execute();
$totalResult = $stmtTotal->fetch(PDO::FETCH_ASSOC);
$currentTotal = $totalResult['total'] ? $totalResult['total'] : 0;

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スマートオーダー - Table <?php echo htmlspecialchars($tableNo); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div style="text-align: center; font-size: 1.5em; font-weight: bold; margin-bottom: 20px; padding: 10px; background-color: #eee; border-radius: 5px;">
        テーブルNo. <?php echo htmlspecialchars($tableNo); ?>
    </div>
    
    <!-- Dev Link -->
    <div style="text-align: right; margin-bottom: 10px;">
        <a href="management.php" style="color: #3498db; text-decoration: none;">管理画面へ (Dev)</a>
    </div>

    <h1>メニュー</h1>

    <?php foreach ($categories as $cat): ?>
        <div class="category-section">
            <h2 class="category-title"><?php echo htmlspecialchars($cat['categoryName']); ?></h2>
            <div class="item-list">
                <?php 
                // このカテゴリに属する商品を抽出
                foreach ($items as $item): 
                    if ($item['category'] == $cat['id']):
                ?>
                    <div class="item-card" onclick="confirmOrder(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>')">
                        <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                        <span class="item-price">¥<?php echo number_format($item['price']); ?></span>
                    </div>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div style="height: 80px;"></div> <!-- Spacer for footer -->
</div>

<div class="footer-bar">
    現在の合計金額: ¥<?php echo number_format($currentTotal); ?>
</div>

<!-- Modal Dialog -->
<div id="orderModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <h3 id="modalItemName">商品名</h3>
        <p>この商品を注文しますか？</p>
        
        <div class="quantity-selector">
            <p>個数を選択:</p>
            <div class="quantity-buttons">
                <button class="btn-qty selected" onclick="selectQuantity(1)">1</button>
                <button class="btn-qty" onclick="selectQuantity(2)">2</button>
                <button class="btn-qty" onclick="selectQuantity(3)">3</button>
                <button class="btn-qty" onclick="selectQuantity(4)">4</button>
            </div>
        </div>

        <div class="modal-buttons">
            <button class="btn-cancel" onclick="closeModal()">キャンセル</button>
            <button class="btn-order" onclick="executeOrder()">注文する</button>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
