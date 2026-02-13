<?php
// Simple test file to debug delete functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Delete Test</h2>";

// Test 1: Check if auth.php loads
echo "<p>1. Loading auth.php... ";
require_once 'auth.php';
echo "OK</p>";

// Test 2: Check session
echo "<p>2. Session status: ";
echo session_status() === PHP_SESSION_ACTIVE ? "Active" : "Not active";
echo "</p>";

// Test 3: Check if logged in
echo "<p>3. Is logged in: ";
echo isLoggedIn() ? "YES" : "NO";
echo "</p>";

// Test 4: Load PDO
echo "<p>4. Loading pdo.php... ";
require_once 'pdo.php';
echo "OK</p>";

// Test 5: Check PDO connection
echo "<p>5. PDO connection: ";
try {
    $test = $pdo->query("SELECT 1");
    echo "OK</p>";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "</p>";
}

// Test 6: Count items before
echo "<p>6. Items in database: ";
$stmt = $pdo->query("SELECT COUNT(*) as cnt FROM sItem");
$row = $stmt->fetch();
echo $row['cnt'] . "</p>";

// Test 7: Try to delete item ID 999 (non-existent)
echo "<p>7. Test delete (ID=999): ";
try {
    $sql = "DELETE FROM sItem WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', 999, PDO::PARAM_INT);
    $stmt->execute();
    echo "Query executed. Rows affected: " . $stmt->rowCount() . "</p>";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "</p>";
}

// Test 8: Show first 5 items
echo "<h3>First 5 items in database:</h3>";
echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Price</th></tr>";
$stmt = $pdo->query("SELECT id, name, price FROM sItem LIMIT 5");
while ($row = $stmt->fetch()) {
    echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['price']}</td></tr>";
}
echo "</table>";

echo "<h3>Test Delete Link:</h3>";
echo "<p>Click to test delete item ID 1: <a href='admin_item_delete.php?id=1'>Delete Item 1</a></p>";
echo "<p>Click to test delete category ID 1: <a href='admin_category_delete.php?id=1'>Delete Category 1</a></p>";
