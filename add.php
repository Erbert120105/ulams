<?php
// ============================================================
//  add.php — Add New Recipe (Standalone Page)
//  Filipino Ulam Recipe System
//
//  NOTE: The main system (home.php) handles adding via modal.
//  This file is kept as a fallback / direct-URL alternative.
//  It posts back to itself, then redirects to home.php.
// ============================================================

ob_start();
require_once 'db.php';

$categories = ['Sabaw','Sarsado','Ginataan','Prito','Inihaw','Gulay','Sizzling'];
$errors     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Collect and sanitize
    $name        = trim($_POST['name']            ?? '');
    $category    = trim($_POST['category']        ?? '');
    $ingredients = trim($_POST['ingredients']     ?? '');
    $procedure   = trim($_POST['procedure_steps'] ?? '');

    // 2. Validate — NO unique checks; multiple recipes per category allowed
    if ($name        === '') $errors[] = 'Kailangan ang pangalan ng recipe.';
    if ($category    === '') $errors[] = 'Piliin ang kategorya.';
    if ($ingredients === '') $errors[] = 'Ilagay ang mga sangkap.';
    if ($procedure   === '') $errors[] = 'Ilagay ang paraan ng pagluto.';

    // 3. Handle optional image upload
    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg','jpeg','png','webp'];
        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Paki-upload ng jpg, jpeg, png, o webp lamang.';
        } else {
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);
            $image_name = uniqid('ulam_', true) . '.' . $ext;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name)) {
                $errors[] = 'Hindi ma-upload ang larawan. Suriin ang folder permissions.';
                $image_name = null;
            }
        }
    }

    // 4. Insert — wrapped in try/catch to surface SQL errors
    if (empty($errors)) {
        try {
            $pdo->prepare(
                "INSERT INTO recipes (name, category, ingredients, procedure_steps, image)
                 VALUES (?, ?, ?, ?, ?)"
            )->execute([$name, $category, $ingredients, $procedure, $image_name]);

            ob_end_clean();
            header('Location: home.php?added=1');
            exit;

        } catch (\PDOException $e) {
            // If you see "Duplicate entry … for key 'category'", run in phpMyAdmin:
            // ALTER TABLE recipes DROP INDEX category;
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magdagdag ng Recipe – Filipino Ulam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --cream:#faf5eb; --parchment:#f2e8d0;
            --brown-lt:#c49a5a; --brown:#8b5e2e; --brown-dk:#5c3a1e;
            --gold:#c8992a; --red:#c0392b; --bg:#f7f2e8;
        }
        body { background:var(--bg); font-family:'Inter',sans-serif; color:var(--brown-dk); }

        .page-header {
            background: linear-gradient(135deg,#2e1504,#6b3010);
            padding: 48px 0 60px; text-align:center;
        }
        .page-header h1 {
            font-family:'Playfair Display',serif; font-size:32px; font-weight:900; color:#f5ead6;
        }
        .page-header h1 span { color:var(--gold); }
        .page-header p { color:rgba(200,153,42,0.7); font-style:italic; margin:6px 0 0; }

        .form-card {
            background:#fff; border-radius:20px;
            box-shadow:0 8px 40px rgba(0,0,0,0.10);
            padding:36px 40px; margin:-24px auto 48px;
            max-width:700px; position:relative;
        }
        .fm-label {
            font-size:11px; font-weight:700; text-transform:uppercase;
            letter-spacing:1px; color:var(--brown); margin-bottom:5px; display:block;
        }
        .fm-ctrl {
            width:100%; padding:10px 14px; border:1.5px solid #ddd;
            border-radius:10px; font-size:13.5px; outline:none;
            transition:border 0.2s; color:var(--brown-dk);
        }
        .fm-ctrl:focus { border-color:var(--gold); box-shadow:0 0 0 3px rgba(200,153,42,0.12); }
        select.fm-ctrl { cursor:pointer; }
        textarea.fm-ctrl { resize:vertical; min-height:100px; }

        .img-zone {
            border:2px dashed #ddd; border-radius:12px; padding:20px;
            text-align:center; cursor:pointer; background:var(--cream);
            transition:border-color 0.2s;
        }
        .img-zone:hover { border-color:var(--gold); }
        .img-zone i { font-size:36px; color:var(--brown-lt); opacity:0.4; }
        .img-zone p { font-size:12.5px; color:#aaa; margin:8px 0 0; }
        .img-prev { max-height:180px; border-radius:8px; display:none; margin:10px auto 0; }

        .btn-submit {
            background:linear-gradient(135deg,#2e1504,#6b3010); color:#fff; border:none;
            padding:12px 32px; border-radius:50px; font-size:14px; font-weight:700;
            cursor:pointer; transition:all 0.25s;
            box-shadow:0 4px 16px rgba(91,57,30,0.3);
        }
        .btn-submit:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(91,57,30,0.4); }
        .btn-back {
            color:var(--brown); text-decoration:none;
            display:inline-flex; align-items:center; gap:6px;
            padding:10px 22px; border:1.5px solid var(--brown-lt);
            border-radius:50px; font-size:13.5px; font-weight:600;
            transition:all 0.2s;
        }
        .btn-back:hover { background:var(--brown); color:#fff; }

        .divider {
            display:flex; align-items:center; gap:12px;
            font-family:'Playfair Display',serif; font-size:14px; font-weight:700;
            color:var(--brown); margin:24px 0 18px;
        }
        .divider::before,.divider::after { content:''; flex:1; height:1px; background:#e0d5c5; }
    </style>
</head>
<body>

<div class="page-header">
    <h1>Magdagdag ng <span>Bagong Ulam</span></h1>
    <p>Ibahagi ang iyong paboritong lutuin sa lahat</p>
</div>

<div class="container" style="max-width:740px;">
    <div class="form-card">

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger rounded-3 mb-4">
            <strong><i class="fas fa-exclamation-circle me-1"></i> May error:</strong>
            <ul class="mb-0 mt-1">
                <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" novalidate>

            <div class="divider">Impormasyon ng Recipe</div>

            <div class="mb-3">
                <label class="fm-label">Pangalan ng Ulam <span style="color:var(--red)">*</span></label>
                <input type="text" name="name" class="fm-ctrl"
                       placeholder="hal. Sinigang na Baboy, Adobong Manok…"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="fm-label">Kategorya <span style="color:var(--red)">*</span></label>
                <select name="category" class="fm-ctrl" required>
                    <option value="">— Piliin ang Kategorya —</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat ?>"
                            <?= (($_POST['category'] ?? '') === $cat) ? 'selected' : '' ?>>
                        <?= $cat ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="divider">Larawan</div>

            <div class="mb-3">
                <div class="img-zone" onclick="document.getElementById('imgInput').click()">
                    <i class="fas fa-camera"></i>
                    <p>I-click para mag-upload ng larawan (JPG, PNG, WEBP)</p>
                    <img id="imgPreview" class="img-prev" src="" alt="Preview">
                </div>
                <input type="file" name="image" id="imgInput"
                       accept=".jpg,.jpeg,.png,.webp" style="display:none">
            </div>

            <div class="divider">Mga Detalye</div>

            <div class="mb-3">
                <label class="fm-label">Mga Sangkap <span style="color:var(--red)">*</span></label>
                <textarea name="ingredients" class="fm-ctrl"
                          placeholder="Isang sangkap bawat linya:&#10;500g baboy&#10;3 butil ng bawang&#10;1 sibuyas" required><?= htmlspecialchars($_POST['ingredients'] ?? '') ?></textarea>
                <div style="font-size:11.5px;color:#aaa;margin-top:4px;">Isang sangkap bawat linya</div>
            </div>

            <div class="mb-4">
                <label class="fm-label">Paraan ng Pagluto <span style="color:var(--red)">*</span></label>
                <textarea name="procedure_steps" class="fm-ctrl" style="min-height:130px;"
                          placeholder="Isang hakbang bawat linya:&#10;Igisa ang bawang at sibuyas.&#10;Ilagay ang karne at lutuin." required><?= htmlspecialchars($_POST['procedure_steps'] ?? '') ?></textarea>
                <div style="font-size:11.5px;color:#aaa;margin-top:4px;">Isang hakbang bawat linya</div>
            </div>

            <div class="d-flex align-items-center gap-3 flex-wrap">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save me-1"></i> I-save ang Recipe
                </button>
                <a href="home.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Bumalik
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('imgInput').addEventListener('change', function () {
    if (!this.files || !this.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('imgPreview');
        img.src = e.target.result;
        img.style.display = 'block';
        document.querySelector('.img-zone i').style.display = 'none';
        document.querySelector('.img-zone p').style.display = 'none';
    };
    reader.readAsDataURL(this.files[0]);
});
</script>
</body>
</html>