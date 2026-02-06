<?php
require_once 'auth.php';
requireAuth();
requireCsrf();

require_once 'pdo.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit;
}

try {
    $pdo->beginTransaction();

    $id = isset($_POST['id']) && $_POST['id'] ? (int)$_POST['id'] : null;
    $name = $_POST['name'];
    $category = (int)$_POST['category'];
    $price = (int)$_POST['price'];

    if ($id) {
        // Update existing item - NO image column!
        $sql = "UPDATE sItem SET
                name = :name,
                category = :category,
                price = :price
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':category', $category, PDO::PARAM_INT);
        $stmt->bindValue(':price', $price, PDO::PARAM_INT);
        $stmt->execute();

        $itemId = $id;
    } else {
        // Insert new item - NO image column!
        $sql = "INSERT INTO sItem (name, category, price, state)
                VALUES (:name, :category, :price, 1)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':category', $category, PDO::PARAM_INT);
        $stmt->bindValue(':price', $price, PDO::PARAM_INT);
        $stmt->execute();

        $itemId = $pdo->lastInsertId();
    }

    // Teacher's method: Save image as itemImages/[ID].jpg
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_ext, $allowed_ext)) {
            throw new Exception('JPG, PNG, GIF only');
        }

        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            throw new Exception('File too large (max 5MB)');
        }

        // Validate MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($file_tmp);
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($mime_type, $allowed_mimes)) {
            throw new Exception('Invalid image file type');
        }

        // Verify it's a real image
        $image_info = getimagesize($file_tmp);
        if ($image_info === false) {
            throw new Exception('Invalid image file');
        }

        $upload_dir = 'itemImages/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Save as ID.jpg (teacher's method!)
        $new_filename = $itemId . '.jpg';
        $upload_path = $upload_dir . $new_filename;

        // Delete old image if exists
        if (file_exists($upload_path)) {
            unlink($upload_path);
        }

        if (!move_uploaded_file($file_tmp, $upload_path)) {
            throw new Exception('Upload failed');
        }
    }

    $pdo->commit();
    header('Location: admin.php?success=item_saved');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: admin.php?error=' . urlencode($e->getMessage()));
    exit;
}
