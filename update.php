<?php
// ============================================================
//  update.php — Process Edit Form Submission
//  Filipino Ulam Recipe System
//
//  Accepts POST from: edit.php (standalone page)
//  The home.php modal posts directly to home.php instead.
//  After update → redirect to home.php?edited=1
// ============================================================

ob_start();
require_once 'db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: home.php');
    exit;
}

// 1. Collect and sanitize
$id          = (int) ($_POST['id']             ?? 0);
$name        = trim($_POST['name']             ?? '');
$category    = trim($_POST['category']         ?? '');
$ingredients = trim($_POST['ingredients']      ?? '');
$procedure   = trim($_POST['procedure_steps']  ?? '');
$old_image   = trim($_POST['current_image']    ?? '');

// 2. Validate
$errors = [];
if ($id <= 0)      $errors[] = 'Invalid recipe ID.';
if (!$name)        $errors[] = 'Kailangan ang pangalan ng recipe.';
if (!$category)    $errors[] = 'Piliin ang kategorya.';
if (!$ingredients) $errors[] = 'Ilagay ang mga sangkap.';
if (!$procedure)   $errors[] = 'Ilagay ang paraan ng pagluto.';

if (!empty($errors)) {
    // Redirect back to edit page with error; in production store errors in session
    ob_end_clean();
    header('Location: edit.php?id=' . $id . '&error=' . urlencode(implode(' | ', $errors)));
    exit;
}

// 3. Handle optional new image upload
$image_name = $old_image;
$upload_dir = __DIR__ . '/uploads/';

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['jpg','jpeg','png','webp'];
    $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        ob_end_clean();
        header('Location: edit.php?id=' . $id . '&error=' . urlencode('Invalid image type. Use JPG, PNG, or WEBP.'));
        exit;
    }

    if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);

    $new_name = uniqid('ulam_', true) . '.' . $ext;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_name)) {
        // Delete old image file if it exists
        if ($old_image && file_exists($upload_dir . $old_image)) {
            @unlink($upload_dir . $old_image);
        }
        $image_name = $new_name;
    } else {
        ob_end_clean();
        header('Location: edit.php?id=' . $id . '&error=' . urlencode('Image upload failed. Check folder permissions.'));
        exit;
    }
}

// 4. Update the database — NO unique constraints on name or category
try {
    $pdo->prepare(
        "UPDATE recipes
         SET name=?, category=?, ingredients=?, procedure_steps=?, image=?
         WHERE id=?"
    )->execute([$name, $category, $ingredients, $procedure, $image_name, $id]);

    ob_end_clean();
    header('Location: home.php?edited=1');
    exit;

} catch (\PDOException $e) {
    ob_end_clean();
    header('Location: edit.php?id=' . $id . '&error=' . urlencode('DB error: ' . $e->getMessage()));
    exit;
}