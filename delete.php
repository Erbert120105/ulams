<?php
// ============================================================
//  delete.php — Delete a Recipe
//  Filipino Ulam Recipe System
//
//  Called by: home.php → confirmDelete() via window.location
//  Usage:     delete.php?id=5
//  After deletion → redirect to home.php?deleted=1
//
//  NOTE: home.php now handles deletion internally via ?delete=ID.
//  This standalone file is kept for backward compatibility and
//  direct-URL access (e.g. linked from old card buttons).
// ============================================================

require_once 'db.php';

// Only accept GET with a valid id
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: home.php');
    exit;
}

$upload_dir = __DIR__ . '/uploads/';

try {
    // 1. Fetch the recipe's image filename before deleting
    $stmt = $pdo->prepare("SELECT image FROM recipes WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $recipe = $stmt->fetch();

    if ($recipe) {
        // 2. Delete the image file from disk (if it exists)
        if (!empty($recipe['image'])) {
            $img_path = $upload_dir . $recipe['image'];
            if (file_exists($img_path)) {
                @unlink($img_path);
            }
        }

        // 3. Delete the database record
        $pdo->prepare("DELETE FROM recipes WHERE id = ?")->execute([$id]);
    }

    header('Location: home.php?deleted=1');
    exit;

} catch (\PDOException $e) {
    // On DB error, redirect with error message
    header('Location: home.php?delerror=' . urlencode($e->getMessage()));
    exit;
}