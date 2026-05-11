<?php
// ============================================================
//  edit.php — Edit Existing Recipe (Standalone Page)
//  Filipino Ulam Recipe System
//
//  NOTE: The main system (home.php) handles editing via modal.
//  This file is kept as a fallback / direct-URL alternative.
//  Usage: edit.php?id=5
// ============================================================

require_once 'db.php';

$categories = ['Sabaw','Sarsado','Ginataan','Prito','Inihaw','Gulay','Sizzling'];
$errors     = [];

// Validate the id from the URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: home.php');
    exit;
}

// Fetch the recipe to pre-fill the form
$stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$recipe = $stmt->fetch();

if (!$recipe) {
    header('Location: home.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-edit ang Recipe – Filipino Ulam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --cream:#faf5eb; --parchment:#f2e8d0;
            --brown-lt:#c49a5a; --brown:#8b5e2e; --brown-dk:#5c3a1e;
            --gold:#c8992a; --red:#c0392b; --bg:#f7f2e8;
            --green-dk:#1a3a1a; --green:#2d6b2d;
        }
        body { background:var(--bg); font-family:'Inter',sans-serif; color:var(--brown-dk); }

        .page-header {
            background: linear-gradient(135deg,var(--green-dk),var(--green));
            padding: 48px 0 60px; text-align:center;
        }
        .page-header h1 {
            font-family:'Playfair Display',serif; font-size:32px; font-weight:900; color:#e8f5e8;
        }
        .page-header h1 span { color:#90ee90; }
        .page-header p { color:rgba(150,220,150,0.75); font-style:italic; margin:6px 0 0; font-size:14px; }

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
            border:2px dashed #ddd; border-radius:12px; padding:16px;
            text-align:center; cursor:pointer; background:var(--cream);
            transition:border-color 0.2s;
        }
        .img-zone:hover { border-color:var(--gold); }
        .img-zone p { font-size:12px; color:#aaa; margin:6px 0 0; }
        .current-img { max-height:180px; border-radius:8px; display:block; margin:0 auto; }
        .img-prev { max-height:180px; border-radius:8px; display:none; margin:8px auto 0; }

        .btn-submit {
            background:linear-gradient(135deg,var(--green-dk),var(--green)); color:#fff; border:none;
            padding:12px 32px; border-radius:50px; font-size:14px; font-weight:700;
            cursor:pointer; transition:all 0.25s;
            box-shadow:0 4px 16px rgba(30,80,30,0.3);
        }
        .btn-submit:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(30,80,30,0.4); }
        .btn-back {
            color:var(--brown); text-decoration:none;
            display:inline-flex; align-items:center; gap:6px;
            padding:10px 22px; border:1.5px solid var(--brown-lt);
            border-radius:50px; font-size:13.5px; font-weight:600; transition:all 0.2s;
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
    <h1>I-<span>edit</span> ang Recipe</h1>
    <p>Ine-edit: "<?= htmlspecialchars($recipe['name']) ?>"</p>
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

        <form method="POST" action="update.php" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="id"            value="<?= $recipe['id'] ?>">
            <input type="hidden" name="current_image" value="<?= htmlspecialchars($recipe['image'] ?? '') ?>">

            <div class="divider">Impormasyon ng Recipe</div>

            <div class="mb-3">
                <label class="fm-label">Pangalan ng Ulam <span style="color:var(--red)">*</span></label>
                <input type="text" name="name" class="fm-ctrl"
                       value="<?= htmlspecialchars($recipe['name']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="fm-label">Kategorya <span style="color:var(--red)">*</span></label>
                <select name="category" class="fm-ctrl" required>
                    <option value="">— Piliin ang Kategorya —</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat ?>" <?= $recipe['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="divider">Larawan</div>

            <div class="mb-3">
                <div class="img-zone" onclick="document.getElementById('imgInput').click()">
                    <?php if ($recipe['image'] && file_exists('uploads/' . $recipe['image'])): ?>
                        <img id="imgPreview" class="current-img" src="uploads/<?= htmlspecialchars($recipe['image']) ?>" alt="Current">
                        <p style="margin-top:8px;">I-click para palitan ang larawan</p>
                    <?php else: ?>
                        <i class="fas fa-camera" style="font-size:34px;color:#c49a5a;opacity:0.4;"></i>
                        <p>I-click para mag-upload ng larawan (JPG, PNG, WEBP)</p>
                        <img id="imgPreview" class="img-prev" src="" alt="Preview">
                    <?php endif; ?>
                </div>
                <input type="file" name="image" id="imgInput"
                       accept=".jpg,.jpeg,.png,.webp" style="display:none">
            </div>

            <div class="divider">Mga Detalye</div>

            <div class="mb-3">
                <label class="fm-label">Mga Sangkap <span style="color:var(--red)">*</span></label>
                <textarea name="ingredients" class="fm-ctrl" required><?= htmlspecialchars($recipe['ingredients']) ?></textarea>
                <div style="font-size:11.5px;color:#aaa;margin-top:4px;">Isang sangkap bawat linya</div>
            </div>

            <div class="mb-4">
                <label class="fm-label">Paraan ng Pagluto <span style="color:var(--red)">*</span></label>
                <textarea name="procedure_steps" class="fm-ctrl" style="min-height:130px;" required><?= htmlspecialchars($recipe['procedure_steps']) ?></textarea>
                <div style="font-size:11.5px;color:#aaa;margin-top:4px;">Isang hakbang bawat linya</div>
            </div>

            <div class="d-flex align-items-center gap-3 flex-wrap">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save me-1"></i> I-update ang Recipe
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
        img.className = 'current-img'; // make it consistently sized
    };
    reader.readAsDataURL(this.files[0]);
});
</script>
</body>
</html>