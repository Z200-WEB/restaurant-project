<?php
// UTF-8 ENCODING - MUST BE FIRST!
header('Content-Type: text/html; charset=UTF-8');

// Load database connection
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

// 現在の注文内容取得 (カート) - state=0の下書きのみ
$sqlCart = "
    SELECT 
        o.id as orderId,
        o.itemNo,
        o.amount,
        i.id as itemId,
        i.name,
        i.price,
        (i.price * o.amount) as subtotal
    FROM sManagement m
    JOIN sOrder o ON m.orderNo = o.orderNo
    JOIN sItem i ON o.itemNo = i.id
    WHERE m.tableNo = :tableNo AND m.state = 0
    ORDER BY o.id DESC
";
$stmtCart = $pdo->prepare($sqlCart);
$stmtCart->bindValue(':tableNo', $tableNo, PDO::PARAM_INT);
$stmtCart->execute();
$cartItems = $stmtCart->fetchAll(PDO::FETCH_ASSOC);

// 合計金額計算
$currentTotal = 0;
$itemCount = 0;
foreach ($cartItems as $item) {
    $currentTotal += $item['subtotal'];
    $itemCount += $item['amount'];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🍽️ Smart Order - Table <?php echo htmlspecialchars($tableNo); ?></title>
    
    <!-- Design System CSS -->
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/animations.css">
    
    <style>
        /* ========== GLOBAL STYLES ========== */
        body {
            background: linear-gradient(135deg, #FFF5E6 0%, #FFEFD5 100%);
            min-height: 100vh;
        }
        
        /* ========== HEADER STYLES ========== */
        .top-header {
            background: linear-gradient(135deg, var(--mc-red) 0%, var(--mc-red-dark) 100%);
            color: white;
            padding: var(--space-4) var(--space-6);
            box-shadow: var(--shadow-lg);
            position: sticky;
            top: 0;
            z-index: var(--z-sticky);
            animation: slideInDown 0.5s ease-out;
        }
        
        .header-content {
            max-width: 1600px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: var(--text-3xl);
            font-weight: var(--weight-black);
            font-family: var(--font-heading);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }
        
        .logo-icon {
            font-size: var(--text-4xl);
            animation: bounce 2s ease-in-out infinite;
        }
        
        .table-badge {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            padding: var(--space-3) var(--space-6);
            border-radius: var(--radius-full);
            font-weight: var(--weight-bold);
            font-size: var(--text-lg);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        /* ========== MAIN CONTAINER ========== */
        .main-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: var(--space-6);
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: var(--space-6);
            min-height: calc(100vh - 80px);
        }
        
        /* ========== MENU SECTION ========== */
        .menu-section {
            background: white;
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            animation: fadeInLeft 0.6s ease-out;
            margin-top: 0;
        }
        
        /* ========== CATEGORY NAVIGATION ========== */
        .category-nav {
            position: sticky;
            top: 0;
            background: white;
            padding: var(--space-5);
            border-bottom: 3px solid var(--mc-red);
            box-shadow: var(--shadow-md);
            z-index: 200;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            max-width: 1600px;
            margin: 0 auto;
        }
        
        .category-nav::-webkit-scrollbar {
            height: 6px;
        }
        
        .category-nav::-webkit-scrollbar-track {
            background: var(--gray-100);
            border-radius: var(--radius);
        }
        
        .category-nav::-webkit-scrollbar-thumb {
            background: var(--mc-red);
            border-radius: var(--radius);
        }
        
        .category-filters {
            display: flex;
            gap: var(--space-3);
            flex-wrap: nowrap;
        }
        
        .cat-btn {
            flex-shrink: 0;
            padding: var(--space-3) var(--space-5);
            background: white;
            border: 2px solid var(--gray-300);
            border-radius: var(--radius-full);
            font-weight: var(--weight-semibold);
            font-size: var(--text-base);
            color: var(--text-primary);
            cursor: pointer;
            transition: all var(--transition-base);
            white-space: nowrap;
        }
        
        .cat-btn:hover {
            border-color: var(--mc-red);
            color: var(--mc-red);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
        
        .cat-btn.active {
            background: linear-gradient(135deg, var(--mc-red) 0%, var(--mc-red-dark) 100%);
            color: white;
            border-color: var(--mc-red);
            box-shadow: var(--shadow-md), var(--glow-red);
        }
        
        /* ========== MENU ITEMS GRID ========== */
        .menu-content {
            padding: var(--space-6);
            position: relative;
            z-index: 1;
        }
        
        .category-section {
            margin-bottom: var(--space-10);
        }
        
        .category-title {
            font-size: var(--text-2xl);
            font-weight: var(--weight-bold);
            color: var(--text-primary);
            margin-bottom: var(--space-5);
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding-left: var(--space-3);
            border-left: 5px solid var(--mc-red);
        }
        
        .item-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: var(--space-5);
        }
        
        /* ========== ITEM CARD ========== */
        .item-card {
            background: white;
            border-radius: var(--radius-xl);
            overflow: hidden;
            cursor: pointer;
            transition: all var(--transition-base);
            box-shadow: var(--shadow-sm);
            border: 2px solid transparent;
            position: relative;
        }
        
        .item-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-xl);
            border-color: var(--mc-yellow);
        }
        
        .item-image-container {
            position: relative;
            width: 100%;
            padding-top: 75%;
            background: linear-gradient(135deg, #FFF5E6 0%, #FFE4B5 100%);
            overflow: hidden;
        }
        
        .item-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-slow);
        }
        
        .item-card:hover .item-image {
            transform: scale(1.1);
        }
        
        /* ========== ITEM BADGES ========== */
        .item-badge {
            position: absolute;
            top: var(--space-3);
            right: var(--space-3);
            padding: var(--space-2) var(--space-4);
            border-radius: var(--radius-full);
            font-size: var(--text-xs);
            font-weight: var(--weight-bold);
            box-shadow: var(--shadow-md);
            z-index: 10;
            animation: bounce 2s ease-in-out infinite;
        }
        
        .badge-limited {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #8B4513;
        }
        
        .badge-recommend {
            background: linear-gradient(135deg, var(--mc-red) 0%, var(--mc-red-dark) 100%);
            color: white;
        }
        
        .badge-new {
            background: linear-gradient(135deg, var(--green) 0%, var(--green-dark) 100%);
            color: white;
        }
        
        /* ========== ITEM INFO ========== */
        .item-info {
            padding: var(--space-4);
        }
        
        .item-name {
            font-weight: var(--weight-bold);
            font-size: var(--text-base);
            color: var(--text-primary);
            margin-bottom: var(--space-2);
            line-height: var(--leading-tight);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.5em;
        }
        
        .item-price {
            color: var(--mc-red);
            font-weight: var(--weight-bold);
            font-size: var(--text-xl);
            margin-bottom: var(--space-3);
        }
        
        .item-add-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--mc-yellow) 0%, var(--mc-yellow-dark) 100%);
            color: var(--dark);
            padding: var(--space-3) var(--space-4);
            border: none;
            border-radius: var(--radius-full);
            font-weight: var(--weight-bold);
            font-size: var(--text-sm);
            cursor: pointer;
            transition: all var(--transition-base);
            box-shadow: var(--shadow-sm);
        }
        
        .item-add-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md), var(--glow-yellow);
        }
        
        /* ========== CART SECTION ========== */
        .cart-section {
            background: white;
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-xl);
            padding: var(--space-6);
            position: sticky;
            top: 100px;
            max-height: calc(100vh - 120px);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            animation: fadeInRight 0.6s ease-out;
        }
        
        .cart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-5);
            padding-bottom: var(--space-4);
            border-bottom: 3px solid var(--mc-red);
        }
        
        .cart-title {
            font-size: var(--text-2xl);
            font-weight: var(--weight-bold);
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }
        
        .cart-count {
            background: var(--mc-red);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--text-sm);
            font-weight: var(--weight-bold);
        }
        
        /* ========== CART ITEMS ========== */
        .cart-items {
            flex: 1;
            overflow-y: auto;
            margin-bottom: var(--space-5);
        }
        
        .cart-empty {
            text-align: center;
            padding: var(--space-10) var(--space-5);
            color: var(--text-secondary);
        }
        
        .cart-empty-icon {
            font-size: var(--text-6xl);
            margin-bottom: var(--space-4);
            opacity: 0.5;
        }
        
        .cart-empty-text {
            font-size: var(--text-base);
            color: var(--text-tertiary);
        }
        
        .cart-item {
            display: flex;
            gap: var(--space-3);
            padding: var(--space-4);
            margin-bottom: var(--space-3);
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            transition: all var(--transition-base);
            animation: fadeInUp 0.3s ease-out;
        }
        
        .cart-item:hover {
            background: var(--gray-100);
            box-shadow: var(--shadow-sm);
        }
        
        .cart-item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: var(--radius-md);
            flex-shrink: 0;
        }
        
        .cart-item-info {
            flex: 1;
            min-width: 0;
        }
        
        .cart-item-name {
            font-weight: var(--weight-semibold);
            font-size: var(--text-sm);
            color: var(--text-primary);
            margin-bottom: var(--space-1);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .cart-item-price {
            color: var(--text-secondary);
            font-size: var(--text-sm);
        }
        
        /* ========== QUANTITY CONTROLS ========== */
        .cart-item-actions {
            display: flex;
            flex-direction: column;
            gap: var(--space-2);
            align-items: flex-end;
        }
        
        .qty-controls {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            background: white;
            padding: var(--space-1);
            border-radius: var(--radius-full);
            box-shadow: var(--shadow-sm);
        }
        
        .qty-btn {
            width: 28px;
            height: 28px;
            background: var(--mc-red);
            color: white;
            border: none;
            border-radius: var(--radius-full);
            font-size: var(--text-base);
            font-weight: var(--weight-bold);
            cursor: pointer;
            transition: all var(--transition-base);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .qty-btn:hover {
            background: var(--mc-red-dark);
            transform: scale(1.1);
        }
        
        .qty-display {
            min-width: 30px;
            text-align: center;
            font-weight: var(--weight-bold);
            font-size: var(--text-base);
        }
        
        .remove-btn {
            background: none;
            border: none;
            color: var(--error);
            cursor: pointer;
            font-size: var(--text-xs);
            font-weight: var(--weight-semibold);
            padding: var(--space-1) var(--space-2);
            border-radius: var(--radius);
            transition: all var(--transition-base);
        }
        
        .remove-btn:hover {
            background: var(--error);
            color: white;
        }
        
        /* ========== CART SUMMARY ========== */
        .cart-summary {
            border-top: 2px solid var(--gray-200);
            padding-top: var(--space-5);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: var(--space-3);
            font-size: var(--text-base);
            color: var(--text-secondary);
        }
        
        .summary-total {
            background: linear-gradient(135deg, var(--mc-red) 0%, var(--mc-red-dark) 100%);
            color: white;
            padding: var(--space-4);
            border-radius: var(--radius-lg);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-4);
            box-shadow: var(--shadow-md);
        }
        
        .summary-total-label {
            font-size: var(--text-lg);
            font-weight: var(--weight-semibold);
        }
        
        .summary-total-amount {
            font-size: var(--text-3xl);
            font-weight: var(--weight-black);
        }
        
        .checkout-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--mc-yellow) 0%, var(--mc-yellow-dark) 100%);
            color: var(--dark);
            padding: var(--space-5) var(--space-6);
            border: none;
            border-radius: var(--radius-full);
            font-size: var(--text-xl);
            font-weight: var(--weight-black);
            cursor: pointer;
            transition: all var(--transition-base);
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-3);
        }
        
        .checkout-btn:hover:not(:disabled) {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl), var(--glow-yellow);
        }
        
        .checkout-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* ========== MODAL STYLES ========== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: var(--z-modal-backdrop);
            animation: fadeIn 0.2s ease-out;
        }
        
        .modal-overlay.show {
            display: flex;
        }
        
        .modal-content {
            background: white;
            padding: var(--space-8);
            border-radius: var(--radius-2xl);
            width: 90%;
            max-width: 450px;
            box-shadow: var(--shadow-2xl);
            animation: scaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        .modal-title {
            font-size: var(--text-2xl);
            font-weight: var(--weight-bold);
            color: var(--text-primary);
            margin-bottom: var(--space-2);
            text-align: center;
        }
        
        .modal-price {
            font-size: var(--text-3xl);
            font-weight: var(--weight-black);
            color: var(--mc-red);
            text-align: center;
            margin-bottom: var(--space-6);
        }
        
        .quantity-selector {
            margin-bottom: var(--space-6);
        }
        
        .quantity-label {
            font-weight: var(--weight-semibold);
            margin-bottom: var(--space-3);
            text-align: center;
            color: var(--text-secondary);
        }
        
        .quantity-buttons {
            display: flex;
            gap: var(--space-3);
            justify-content: center;
        }
        
        .btn-qty-modal {
            width: 60px;
            height: 60px;
            background: white;
            border: 3px solid var(--gray-300);
            border-radius: var(--radius-lg);
            font-size: var(--text-xl);
            font-weight: var(--weight-bold);
            color: var(--text-primary);
            cursor: pointer;
            transition: all var(--transition-base);
        }
        
        .btn-qty-modal:hover {
            border-color: var(--mc-red);
            color: var(--mc-red);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .btn-qty-modal.selected {
            background: linear-gradient(135deg, var(--mc-red) 0%, var(--mc-red-dark) 100%);
            color: white;
            border-color: var(--mc-red);
            transform: scale(1.1);
            box-shadow: var(--shadow-md), var(--glow-red);
        }
        
        .modal-buttons {
            display: flex;
            gap: var(--space-4);
        }
        
        .btn-cancel,
        .btn-order,
        .btn-confirm-delete {
            flex: 1;
            padding: var(--space-4) var(--space-6);
            border: none;
            border-radius: var(--radius-full);
            font-size: var(--text-lg);
            font-weight: var(--weight-bold);
            cursor: pointer;
            transition: all var(--transition-base);
        }
        
        .btn-cancel {
            background: var(--gray-400);
            color: white;
        }
        
        .btn-cancel:hover {
            background: var(--gray-500);
        }
        
        .btn-order {
            background: linear-gradient(135deg, var(--mc-red) 0%, var(--mc-red-dark) 100%);
            color: white;
            box-shadow: var(--shadow-md);
        }
        
        .btn-order:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg), var(--glow-red);
        }
        
        .btn-confirm-delete {
            background: linear-gradient(135deg, var(--error) 0%, #C0392B 100%);
            color: white;
            box-shadow: var(--shadow-md);
        }
        
        .btn-confirm-delete:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg), 0 0 20px rgba(231, 76, 60, 0.3);
        }
        
        /* ========== CONFIRM DELETE MODAL ========== */
        .confirm-modal-content {
            background: white;
            padding: var(--space-8);
            border-radius: var(--radius-2xl);
            width: 90%;
            max-width: 450px;
            box-shadow: var(--shadow-2xl);
            animation: scaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            text-align: center;
        }
        
        .confirm-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto var(--space-5);
            background: linear-gradient(135deg, var(--warning) 0%, #D68910 100%);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--text-5xl);
            color: white;
            box-shadow: var(--shadow-lg);
        }
        
        .confirm-title {
            font-size: var(--text-2xl);
            font-weight: var(--weight-black);
            color: var(--text-primary);
            margin-bottom: var(--space-3);
        }
        
        .confirm-message {
            font-size: var(--text-lg);
            color: var(--text-secondary);
            margin-bottom: var(--space-2);
        }
        
        .confirm-item-name {
            font-size: var(--text-xl);
            font-weight: var(--weight-bold);
            color: var(--mc-red);
            margin-bottom: var(--space-6);
        }
        
        /* ========== CHECKOUT SUCCESS MODAL ========== */
        .checkout-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: var(--z-modal);
            justify-content: center;
            align-items: center;
        }
        
        .checkout-modal.show {
            display: flex;
        }
        
        .checkout-modal-content {
            background: white;
            border-radius: var(--radius-2xl);
            padding: var(--space-10);
            width: 90%;
            max-width: 600px;
            box-shadow: var(--shadow-2xl);
            text-align: center;
            animation: scaleIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto var(--space-6);
            background: linear-gradient(135deg, var(--green) 0%, var(--green-dark) 100%);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--text-6xl);
            color: white;
            box-shadow: var(--shadow-xl), var(--glow-green);
            animation: bounce 1s ease-in-out;
        }
        
        .checkout-modal-title {
            font-size: var(--text-4xl);
            font-weight: var(--weight-black);
            color: var(--text-primary);
            margin-bottom: var(--space-3);
        }
        
        .checkout-modal-subtitle {
            font-size: var(--text-base);
            color: var(--text-secondary);
            margin-bottom: var(--space-8);
        }
        
        .order-summary {
            background: var(--gray-50);
            border-radius: var(--radius-xl);
            padding: var(--space-6);
            margin-bottom: var(--space-6);
            max-height: 300px;
            overflow-y: auto;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: var(--space-3) 0;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .summary-item:last-child {
            border-bottom: none;
        }
        
        .checkout-total-box {
            background: linear-gradient(135deg, var(--mc-red) 0%, var(--mc-red-dark) 100%);
            color: white;
            padding: var(--space-5);
            border-radius: var(--radius-xl);
            margin-bottom: var(--space-6);
            box-shadow: var(--shadow-lg);
        }
        
        .checkout-total-row {
            display: flex;
            justify-content: space-between;
            font-size: var(--text-2xl);
            font-weight: var(--weight-black);
        }
        
        .btn-confirm-checkout {
            width: 100%;
            background: linear-gradient(135deg, var(--green) 0%, var(--green-dark) 100%);
            color: white;
            padding: var(--space-5) var(--space-6);
            border: none;
            border-radius: var(--radius-full);
            font-size: var(--text-xl);
            font-weight: var(--weight-black);
            cursor: pointer;
            transition: all var(--transition-base);
            box-shadow: var(--shadow-lg);
        }
        
        .btn-confirm-checkout:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-xl), var(--glow-green);
        }
        
        /* ========== TOAST NOTIFICATION SYSTEM ========== */
        .toast-container {
            position: fixed;
            top: 90px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-width: 400px;
        }
        
        .toast {
            background: white;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 320px;
            animation: slideInNotification 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            border-left: 5px solid;
        }
        
        .toast.toast-success {
            border-left-color: var(--green);
        }
        
        .toast.toast-error {
            border-left-color: var(--error);
        }
        
        .toast.toast-warning {
            border-left-color: var(--warning);
        }
        
        .toast.toast-info {
            border-left-color: var(--blue);
        }
        
        .toast-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
            color: white;
            flex-shrink: 0;
        }
        
        .toast-success .toast-icon {
            background: linear-gradient(135deg, var(--green) 0%, var(--green-dark) 100%);
        }
        
        .toast-error .toast-icon {
            background: linear-gradient(135deg, var(--error) 0%, #C0392B 100%);
        }
        
        .toast-warning .toast-icon {
            background: linear-gradient(135deg, var(--warning) 0%, #D68910 100%);
        }
        
        .toast-info .toast-icon {
            background: linear-gradient(135deg, var(--blue) 0%, var(--blue-dark) 100%);
        }
        
        .toast-content {
            flex: 1;
            min-width: 0;
        }
        
        .toast-title {
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 4px;
            color: #333;
        }
        
        .toast-message {
            font-size: 13px;
            color: #666;
        }
        
        .toast-close {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 20px;
            line-height: 1;
            padding: 0;
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            transition: all 0.2s;
        }
        
        .toast-close:hover {
            color: #333;
            transform: rotate(90deg);
        }
        
        .toast.toast-exit {
            animation: slideOutNotification 0.3s ease-out forwards;
        }
        
        /* ========== RESPONSIVE DESIGN ========== */
        @media (max-width: 1200px) {
            .main-container {
                grid-template-columns: 1fr;
            }
            
            .cart-section {
                position: static;
                max-height: none;
            }
            
            .item-grid {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .main-container {
                padding: var(--space-4);
                gap: var(--space-4);
            }
            
            .category-nav {
                padding: var(--space-4);
            }
            
            .item-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: var(--space-3);
            }
            
            .logo {
                font-size: var(--text-xl);
            }
            
            .table-badge {
                font-size: var(--text-base);
                padding: var(--space-2) var(--space-4);
            }
            
            .toast-container {
                left: 10px;
                right: 10px;
                max-width: none;
            }
            
            .toast {
                min-width: auto;
            }
        }
        
        /* ========== LOADING INDICATOR ========== */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: var(--z-modal);
        }
        
        .loading-overlay.show {
            display: flex;
        }
        
        .spinner {
            width: 60px;
            height: 60px;
            border: 6px solid var(--gray-200);
            border-top-color: var(--mc-red);
            border-radius: var(--radius-full);
            animation: spin 0.8s linear infinite;
        }
    </style>
</head>
<body>

<!-- TOAST CONTAINER -->
<div class="toast-container" id="toastContainer"></div>

<!-- TOP HEADER -->
<div class="top-header">
    <div class="header-content">
        <div class="logo">
            <!-- 🎯 CHANGE THIS ICON TO YOUR RESTAURANT TYPE! -->
            <span class="logo-icon">🍽️</span>
            <!-- Other options: 🍜 🍱 🍛 🍕 🍰 🥘 🍣 🥗 -->
            <span>Smart Order</span>
        </div>
        <div class="table-badge">
            🪑 Table No. <?php echo $tableNo; ?>
        </div>
    </div>
</div>

<!-- CATEGORY NAVIGATION - Fixed below header -->
<div class="category-nav">
    <div class="category-filters">
        <?php foreach ($categories as $cat): ?>
            <button class="cat-btn <?php echo ($cat === reset($categories)) ? 'active' : ''; ?>"
                    onclick="filterCategory(<?php echo $cat['id']; ?>, this)">
                <?php echo htmlspecialchars($cat['categoryName']); ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<!-- MAIN CONTAINER -->
<div class="main-container">

    <!-- MENU SECTION -->
    <div class="menu-section">

        <!-- MENU CONTENT -->
        <div class="menu-content">
            <?php foreach ($categories as $cat): ?>
                <div class="category-section" data-category="<?php echo $cat['id']; ?>">
                    <h2 class="category-title">
                        <span>📁</span>
                        <span><?php echo htmlspecialchars($cat['categoryName']); ?></span>
                    </h2>
                    
                    <div class="item-grid stagger-fade-in">
                        <?php foreach ($items as $item): 
                            if ($item['category'] == $cat['id']):
                                $imagePath = 'itemImages/' . $item['id'] . '.jpg';
                                if (!file_exists($imagePath)) {
                                    $imagePath = 'itemImages/default.jpg';
                                }
                                
                                $hasLimited = strpos($item['name'], '[期間限定]') !== false;
                                $hasRecommend = strpos($item['name'], '[おすすめ]') !== false;
                                $displayName = trim(str_replace(['[期間限定]', '[おすすめ]'], '', $item['name']));
                        ?>
                            <div class="item-card hover-lift" 
                                 onclick="openOrderModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($displayName, ENT_QUOTES); ?>', <?php echo $item['price']; ?>)">
                                
                                <div class="item-image-container">
                                    <?php if ($hasLimited): ?>
                                        <span class="item-badge badge-limited">⏰ 期間限定</span>
                                    <?php elseif ($hasRecommend): ?>
                                        <span class="item-badge badge-recommend">⭐ おすすめ</span>
                                    <?php endif; ?>
                                    
                                    <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                         alt="<?php echo htmlspecialchars($displayName); ?>"
                                         class="item-image"
                                         onerror="this.src='itemImages/default.jpg'">
                                </div>
                                
                                <div class="item-info">
                                    <div class="item-name"><?php echo htmlspecialchars($displayName); ?></div>
                                    <div class="item-price">¥<?php echo number_format($item['price']); ?></div>
                                    <button class="item-add-btn" onclick="event.stopPropagation(); openOrderModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($displayName, ENT_QUOTES); ?>', <?php echo $item['price']; ?>)">
                                        🛒 カートに追加
                                    </button>
                                </div>
                            </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- CART SECTION -->
    <div class="cart-section">
        <div class="cart-header">
            <div class="cart-title">
                <span>🛒</span>
                <span>ご注文内容</span>
            </div>
            <?php if ($itemCount > 0): ?>
                <div class="cart-count"><?php echo $itemCount; ?></div>
            <?php endif; ?>
        </div>
        
        <div class="cart-items">
            <?php if (empty($cartItems)): ?>
                <div class="cart-empty">
                    <div class="cart-empty-icon">🍽️</div>
                    <div class="cart-empty-text">商品を追加してください</div>
                </div>
            <?php else: ?>
                <?php foreach ($cartItems as $cartItem): 
                    $itemImagePath = 'itemImages/' . $cartItem['itemId'] . '.jpg';
                    if (!file_exists($itemImagePath)) {
                        $itemImagePath = 'itemImages/default.jpg';
                    }
                ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($itemImagePath); ?>" 
                             alt="<?php echo htmlspecialchars($cartItem['name']); ?>"
                             class="cart-item-image"
                             onerror="this.src='itemImages/default.jpg'">
                        
                        <div class="cart-item-info">
                            <div class="cart-item-name"><?php echo htmlspecialchars($cartItem['name']); ?></div>
                            <div class="cart-item-price">¥<?php echo number_format($cartItem['price']); ?> × <?php echo $cartItem['amount']; ?></div>
                        </div>
                        
                        <div class="cart-item-actions">
                            <div class="qty-controls">
                                <button class="qty-btn" onclick="updateQuantity(<?php echo $cartItem['orderId']; ?>, -1)">−</button>
                                <span class="qty-display"><?php echo $cartItem['amount']; ?></span>
                                <button class="qty-btn" onclick="updateQuantity(<?php echo $cartItem['orderId']; ?>, 1)">+</button>
                            </div>
                            <button class="remove-btn" onclick="confirmRemoveItem(<?php echo $cartItem['orderId']; ?>, '<?php echo htmlspecialchars($cartItem['name'], ENT_QUOTES); ?>')">
                                🗑️ 削除
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($cartItems)): ?>
            <div class="cart-summary">
                <div class="summary-row">
                    <span>商品点数</span>
                    <span><?php echo $itemCount; ?>点</span>
                </div>
                
                <div class="summary-total">
                    <span class="summary-total-label">合計</span>
                    <span class="summary-total-amount">¥<?php echo number_format($currentTotal); ?></span>
                </div>
                
                <button class="checkout-btn" onclick="confirmCheckout()">
                    <span>🍴</span>
                    <span>注文を確定する</span>
                </button>
            </div>
        <?php endif; ?>
    </div>
    
</div>

<!-- ORDER MODAL -->
<div class="modal-overlay" id="orderModal">
    <div class="modal-content">
        <h3 class="modal-title" id="modalItemName">商品名</h3>
        <div class="modal-price" id="modalItemPrice">¥0</div>
        
        <div class="quantity-selector">
            <p class="quantity-label">個数を選択:</p>
            <div class="quantity-buttons">
                <button class="btn-qty-modal selected" onclick="selectQuantity(1)">1</button>
                <button class="btn-qty-modal" onclick="selectQuantity(2)">2</button>
                <button class="btn-qty-modal" onclick="selectQuantity(3)">3</button>
                <button class="btn-qty-modal" onclick="selectQuantity(4)">4</button>
            </div>
        </div>
        
        <div class="modal-buttons">
            <button class="btn-cancel" onclick="closeModal()">キャンセル</button>
            <button class="btn-order" onclick="executeOrder()">注文する</button>
        </div>
    </div>
</div>

<!-- CONFIRM DELETE MODAL -->
<div class="modal-overlay" id="confirmDeleteModal">
    <div class="confirm-modal-content">
        <div class="confirm-icon">⚠️</div>
        <h3 class="confirm-title">本当に削除しますか?</h3>
        <p class="confirm-message">この商品をカートから削除します</p>
        <p class="confirm-item-name" id="confirmItemName">商品名</p>
        
        <div class="modal-buttons">
            <button class="btn-cancel" onclick="closeConfirmDelete()">キャンセル</button>
            <button class="btn-confirm-delete" onclick="executeRemoveItem()">削除する</button>
        </div>
    </div>
</div>

<!-- CHECKOUT SUCCESS MODAL -->
<div class="checkout-modal" id="checkoutModal">
    <div class="checkout-modal-content">
        <div class="success-icon">✓</div>
        <h2 class="checkout-modal-title">ご注文ありがとうございます!</h2>
        <p class="checkout-modal-subtitle">注文番号: <span id="checkoutOrderNo">---</span></p>
        
        <div class="order-summary" id="orderSummaryContent">
            <!-- Items will be inserted here -->
        </div>
        
        <div class="checkout-total-box">
            <div class="checkout-total-row">
                <span>合計金額</span>
                <span id="checkoutTotalAmount">¥0</span>
            </div>
        </div>
        
        <button class="btn-confirm-checkout" onclick="closeCheckoutModal()">OK</button>
    </div>
</div>

<!-- LOADING OVERLAY -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
</div>

<script>
let selectedItemId = null;
let selectedAmount = 1;
let pendingDeleteOrderId = null;
let pendingDeleteItemName = '';
const tableNo = <?php echo $tableNo; ?>;

// ========================================
// TOAST NOTIFICATION SYSTEM
// ========================================
function showToast(type, title, message, duration = 3000) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };
    
    toast.innerHTML = `
        <div class="toast-icon">${icons[type] || 'ℹ'}</div>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.closest('.toast').remove()">×</button>
    `;
    
    container.appendChild(toast);
    
    // Auto remove after duration
    setTimeout(() => {
        toast.classList.add('toast-exit');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Shortcut functions
function showSuccessToast(title, message, duration = 3000) {
    showToast('success', title, message, duration);
}

function showErrorToast(title, message, duration = 3000) {
    showToast('error', title, message, duration);
}

function showWarningToast(title, message, duration = 3000) {
    showToast('warning', title, message, duration);
}

function showInfoToast(title, message, duration = 3000) {
    showToast('info', title, message, duration);
}

// ========================================
// MAIN FUNCTIONS
// ========================================

// Category filter
function filterCategory(catId, btn) {
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    document.querySelectorAll('.category-section').forEach(section => {
        if (section.getAttribute('data-category') == catId) {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    });
}

// Open order modal
function openOrderModal(itemId, itemName, itemPrice) {
    selectedItemId = itemId;
    selectedAmount = 1;
    document.getElementById('modalItemName').textContent = itemName;
    document.getElementById('modalItemPrice').textContent = '¥' + itemPrice.toLocaleString();
    updateQuantityUI();
    document.getElementById('orderModal').classList.add('show');
}

function closeModal() {
    document.getElementById('orderModal').classList.remove('show');
}

function selectQuantity(amount) {
    selectedAmount = amount;
    updateQuantityUI();
}

function updateQuantityUI() {
    document.querySelectorAll('.btn-qty-modal').forEach(btn => {
        if (parseInt(btn.textContent) === selectedAmount) {
            btn.classList.add('selected');
        } else {
            btn.classList.remove('selected');
        }
    });
}

function showLoading() {
    document.getElementById('loadingOverlay').classList.add('show');
}

function hideLoading() {
    document.getElementById('loadingOverlay').classList.remove('show');
}

function executeOrder() {
    if (!selectedItemId) return;
    
    showLoading();
    
    const formData = new FormData();
    formData.append('itemId', selectedItemId);
    formData.append('tableNo', tableNo);
    formData.append('amount', selectedAmount);
    
    fetch('logic.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.status === 'success') {
            closeModal();
            showSuccessToast('カートに追加', '商品をカートに追加しました');
            setTimeout(() => location.reload(), 800);
        } else {
            showErrorToast('注文失敗', data.message);
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showErrorToast('通信エラー', 'サーバーとの通信に失敗しました');
    });
}

function updateQuantity(orderId, change) {
    showLoading();
    
    const formData = new FormData();
    formData.append('orderId', orderId);
    formData.append('change', change);
    
    fetch('cart_update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            location.reload();
        } else {
            showErrorToast('更新失敗', 'カートの更新に失敗しました');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showErrorToast('通信エラー', 'エラーが発生しました');
    });
}

// ========================================
// FIXED DELETE FUNCTION - NO TOAST, DIRECT RELOAD
// ========================================
function confirmRemoveItem(orderId, itemName) {
    pendingDeleteOrderId = orderId;
    pendingDeleteItemName = itemName;
    document.getElementById('confirmItemName').textContent = '「' + itemName + '」';
    document.getElementById('confirmDeleteModal').classList.add('show');
}

function closeConfirmDelete() {
    document.getElementById('confirmDeleteModal').classList.remove('show');
    pendingDeleteOrderId = null;
    pendingDeleteItemName = '';
}

function executeRemoveItem() {
    if (!pendingDeleteOrderId) return;
    
    const orderIdToDelete = pendingDeleteOrderId;
    closeConfirmDelete();
    showLoading();
    
    const formData = new FormData();
    formData.append('orderId', orderIdToDelete);
    
    fetch('cart_remove.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Server error: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Delete response:', data); // Debug log
        if (data.success) {
            // Reload immediately without toast to ensure item is removed
            location.reload();
        } else {
            hideLoading();
            showErrorToast('削除失敗', data.message || '削除に失敗しました');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Delete error:', error);
        showErrorToast('通信エラー', 'エラーが発生しました: ' + error.message);
    });
}

function confirmCheckout() {
    <?php if (empty($cartItems)): ?>
        showWarningToast('カートが空です', '商品を追加してください');
        return;
    <?php endif; ?>
    
    showLoading();
    
    const formData = new FormData();
    formData.append('tableNo', tableNo);
    
    fetch('checkout.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            // Build order summary
            let summaryHTML = '';
            <?php foreach ($cartItems as $item): ?>
                summaryHTML += `
                    <div class="summary-item">
                        <span><?php echo htmlspecialchars(trim(str_replace(['[期間限定]', '[おすすめ]'], '', $item['name']))); ?> × <?php echo $item['amount']; ?></span>
                        <span>¥<?php echo number_format($item['subtotal']); ?></span>
                    </div>
                `;
            <?php endforeach; ?>
            
            document.getElementById('orderSummaryContent').innerHTML = summaryHTML;
            document.getElementById('checkoutTotalAmount').textContent = '¥<?php echo number_format($currentTotal); ?>';
            document.getElementById('checkoutOrderNo').textContent = data.orderNo;
            document.getElementById('checkoutModal').classList.add('show');
        } else {
            showErrorToast('注文エラー', data.message);
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showErrorToast('通信エラー', 'サーバーとの通信に失敗しました');
    });
}

function closeCheckoutModal() {
    document.getElementById('checkoutModal').classList.remove('show');
    location.reload();
}

// Initialize - show first category only
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.category-section');
    if (sections.length > 0) {
        const firstCatId = sections[0].getAttribute('data-category');
        sections.forEach(section => {
            const catId = section.getAttribute('data-category');
            section.style.display = (catId == firstCatId) ? 'block' : 'none';
        });
    }
});

// Close modals on outside click
document.getElementById('orderModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.getElementById('confirmDeleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeConfirmDelete();
});

document.getElementById('checkoutModal').addEventListener('click', function(e) {
    if (e.target === this) closeCheckoutModal();
});
</script>

</body>
</html>