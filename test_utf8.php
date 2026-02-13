<?php
// UTF-8 TEST FILE
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>UTF-8 Test</title>
</head>
<body>
    <h1>UTF-8 Encoding Test</h1>
    
    <h2>If you see clear Japanese below, UTF-8 works:</h2>
    <p style="font-size: 24px;">商品を削除しました</p>
    <p style="font-size: 24px;">カテゴリ管理</p>
    <p style="font-size: 24px;">管理画面</p>
    
    <h2>If you see garbled text, there's a problem</h2>
    
    <hr>
    <p>PHP Version: <?php echo phpversion(); ?></p>
    <p>Default Charset: <?php echo ini_get('default_charset'); ?></p>
</body>
</html>