<?php
// pdo.php の説明

// PDOとはPHP Data Objectsのことで
// おもにPHPでデータベースに接続するために使われる仕組みである

// Oracle, MySQL, MariaDB, PostgreSQLなどいろいろなデータベースがあるが
// PDOで接続するための仕組みだけ作っておくと
// 実際のロジック部分（実務部分）は、普通のSQLを書くだけでOKになる
// したがってデータベースがなにか？てのを意識せず一般的なSQLで大丈夫になる
// データベースが変わったら、この pdo.php だけを少し修正すればいい

// まずtry catch 構文
// データベースへの接続など大掛かりな試みで失敗の可能性がある機能を使うときは
// try catch構文を使う
// 失敗したときにしっかりとエラー処理ができるため
// データベースに接続しかけた途中で中途半端になってしまうことがない

try {
  // データベースに接続するために必要な最低限4つの情報を定義
  // ホストアドレス（グローバルなサーバではしっかりとしたサーバアドレスを入力またはIPアドレスとなる）
  // あとはデータベース名・アカウント・パスワードをそれぞれ文字列変数として記載
  $dbHost =     getenv('MYSQLHOST') ?: "localhost";
  $dbName =     getenv('MYSQLDATABASE') ?: "practice";
  $user =       getenv('MYSQLUSER') ?: "root";
  $password =   getenv('MYSQLPASSWORD') ?: "";
  $dbPort =     getenv('MYSQLPORT') ?: "3306";

  // オプションを定義
  $opt = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
  ];

  // データベースに接続しPDOオブジェクトを生成
  // 生成したものを $pdo に代入しておく
  $pdo = new PDO('mysql:host='.$dbHost.';port='.$dbPort.';dbname='.$dbName,$user,$password,$opt);
}
catch(PDOException $e) {
  // さっきのtryでミスっていたらここでエラーを受け取る
  // tryで仕掛けていたことは無効になる
  header('Content-Type: text/plain; charset=UTF-8', true, 500);
  exit($e->getMessage());
}

// tryで生成したPDOオブジェクトが入っている変数を戻り値として戻る
return $pdo;
