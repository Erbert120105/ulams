<?php
// ============================================================
//  delete.php — Delete a Recipe
//  Filipino Ulam Recipe System
//
//  Accepts:  ?id=5   OR   ?delete=5  (both work)
//  Deletes the recipe record + its uploaded image file
//  Redirects to home.php?deleted=1 on success
// ============================================================

ob_start(); // Buffer output so header() always works
require_once 'db.php';

// Accept either ?id= or ?delete= parameter
$id = 0;
if (isset($_GET['id']))     $id = (int) $_GET['id'];
if (isset($_GET['delete'])) $id = (int) $_GET['delete'];

// Reject invalid id
if ($id <= 0) {
    ob_end_clean();
    header('Location: home.php');
    exit;
}

$upload_dir = __DIR__ . '/uploads/';

try {
    // 1. Fetch the image filename BEFORE deleting the record
    $stmt = $pdo->prepare("SELECT image FROM recipes WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $recipe = $stmt->fetch();

    if ($recipe) {
        // 2. Delete the image file safely (basename prevents path traversal)
        if (!empty($recipe['image'])) {
            $img_path = $upload_dir . basename($recipe['image']);
            if (file_exists($img_path)) {
                @unlink($img_path);
            }
        }

        // 3. Delete the database record
        $pdo->prepare("DELETE FROM recipes WHERE id = ?")->execute([$id]);
    }

    ob_end_clean();
    header('Location: home.php?deleted=1');
    exit;

} catch (\PDOException $e) {
    ob_end_clean();
    header('Location: home.php?delerror=' . urlencode($e->getMessage()));
    exit;
}