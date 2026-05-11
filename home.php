<?php
/* ═══════════════════════════════════════════════════════════
   home.php  –  Filipino Ulam Recipe System  (all-in-one)
   Handles: list · add · edit · delete via modal + POST/GET
═══════════════════════════════════════════════════════════ */
ob_start();
require_once 'db.php';

$categories = ['Sabaw','Sarsado','Ginataan','Prito','Inihaw','Gulay','Sizzling'];
$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0775, true);

function handleUpload($field, $upload_dir) {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
    $allowed = ['jpg','jpeg','png','webp'];
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return ['error' => 'Invalid file type. Use JPG, PNG or WEBP.'];
    $name = uniqid('ulam_', true) . '.' . $ext;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $upload_dir . $name))
        return ['error' => 'Upload failed. Check folder permissions.'];
    return $name;
}

$flash = '';

/* ADD */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'add') {
    $name        = trim($_POST['name']            ?? '');
    $category    = trim($_POST['category']        ?? '');
    $ingredients = trim($_POST['ingredients']     ?? '');
    $procedure   = trim($_POST['procedure_steps'] ?? '');
    $errors = [];
    if (!$name)        $errors[] = 'Pangalan ay kailangan.';
    if (!$category)    $errors[] = 'Kategorya ay kailangan.';
    if (!$ingredients) $errors[] = 'Sangkap ay kailangan.';
    if (!$procedure)   $errors[] = 'Paraan ng pagluto ay kailangan.';
    $img = null;
    if (empty($errors)) {
        $result = handleUpload('image', $upload_dir);
        if (is_array($result)) $errors[] = $result['error'];
        else $img = $result;
    }
    if (empty($errors)) {
        try {
            $pdo->prepare("INSERT INTO recipes (name,category,ingredients,procedure_steps,image) VALUES (?,?,?,?,?)")
                ->execute([$name,$category,$ingredients,$procedure,$img]);
            ob_end_clean(); header('Location: home.php?added=1'); exit;
        } catch (\PDOException $e) { $errors[] = 'DB error: '.$e->getMessage(); }
    }
    $flash = '<div class="alert alert-danger py-2 mb-0 small rounded-3"><b>Error:</b> '.implode(' ', array_map('htmlspecialchars',$errors)).'</div>';
}

/* EDIT */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'edit') {
    $id          = (int)($_POST['id']             ?? 0);
    $name        = trim($_POST['name']            ?? '');
    $category    = trim($_POST['category']        ?? '');
    $ingredients = trim($_POST['ingredients']     ?? '');
    $procedure   = trim($_POST['procedure_steps'] ?? '');
    $old_img     = trim($_POST['current_image']   ?? '');
    $errors = [];
    if (!$id)          $errors[] = 'Invalid ID.';
    if (!$name)        $errors[] = 'Pangalan ay kailangan.';
    if (!$category)    $errors[] = 'Kategorya ay kailangan.';
    if (!$ingredients) $errors[] = 'Sangkap ay kailangan.';
    if (!$procedure)   $errors[] = 'Paraan ng pagluto ay kailangan.';
    $img = $old_img;
    if (empty($errors)) {
        $result = handleUpload('image', $upload_dir);
        if (is_array($result)) $errors[] = $result['error'];
        elseif ($result !== null) {
            if ($old_img && file_exists($upload_dir.$old_img)) @unlink($upload_dir.$old_img);
            $img = $result;
        }
    }
    if (empty($errors)) {
        try {
            $pdo->prepare("UPDATE recipes SET name=?,category=?,ingredients=?,procedure_steps=?,image=? WHERE id=?")
                ->execute([$name,$category,$ingredients,$procedure,$img,$id]);
            ob_end_clean(); header('Location: home.php?edited=1'); exit;
        } catch (\PDOException $e) { $errors[] = 'DB error: '.$e->getMessage(); }
    }
    $flash = '<div class="alert alert-danger py-2 mb-0 small rounded-3"><b>Error:</b> '.implode(' ', array_map('htmlspecialchars',$errors)).'</div>';
}

/* DELETE */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id) {
        $row = $pdo->prepare("SELECT image FROM recipes WHERE id=?");
        $row->execute([$id]);
        $rec = $row->fetch();
        if ($rec && $rec['image'] && file_exists($upload_dir.$rec['image'])) @unlink($upload_dir.$rec['image']);
        $pdo->prepare("DELETE FROM recipes WHERE id=?")->execute([$id]);
    }
    ob_end_clean(); header('Location: home.php?deleted=1'); exit;
}

/* FETCH */
$active_cat = trim($_GET['cat'] ?? 'All');
$search     = trim($_GET['q']   ?? '');
$q  = "SELECT * FROM recipes WHERE 1=1";
$p  = [];
if ($active_cat !== 'All') { $q .= " AND category=?"; $p[] = $active_cat; }
if ($search !== '')         { $q .= " AND name LIKE ?"; $p[] = "%$search%"; }
$q .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($q); $stmt->execute($p);
$recipes = $stmt->fetchAll();
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Filipino Ulam Recipes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Inter:wght@300;400;500;600;700&family=Crimson+Text:ital,wght@0,400;1,400&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
/* ──────────────────────────────────────────
   ROOT
────────────────────────────────────────── */
:root{
    --cream:#faf5eb;--parchment:#f2e8d0;
    --brown-lt:#c49a5a;--brown:#8b5e2e;--brown-dk:#5c3a1e;
    --ink:#2c1a0e;--red:#c0392b;--gold:#c8992a;--gold-lt:#f0c040;
    --bg:#f7f2e8;--text:#3a2510;--card-bg:#fff;
    --shadow:0 4px 24px rgba(0,0,0,0.09);
    --hover-shadow:0 16px 48px rgba(0,0,0,0.18);
    --sidebar-w:265px;--r-lg:18px;--r-md:12px;--r-sm:8px;
    --ease:cubic-bezier(0.4,0,0.2,1);
    --spring:cubic-bezier(0.34,1.56,0.64,1);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;overflow-x:hidden;}

/* ──────────────────────────────────────────
   SIDEBAR
────────────────────────────────────────── */
.sidebar{
    width:var(--sidebar-w);min-height:100vh;
    background:linear-gradient(170deg,#2e1504 0%,#4a2209 45%,#3a1a06 100%);
    position:fixed;left:0;top:0;z-index:200;
    display:flex;flex-direction:column;
    box-shadow:5px 0 35px rgba(0,0,0,0.4);
}
.sidebar-brand{padding:26px 20px 18px;border-bottom:1px solid rgba(200,153,42,0.2);}
.brand-logo{font-size:26px;display:block;margin-bottom:5px;}
.brand-title{font-family:'Playfair Display',serif;font-size:19px;font-weight:900;color:#f5ead6;line-height:1.2;}
.brand-title span{color:var(--gold);}
.brand-sub{font-family:'Crimson Text',serif;font-style:italic;font-size:12px;color:rgba(200,153,42,0.6);margin-top:3px;display:block;}

.sidebar-nav{padding:16px 12px;flex:1;overflow-y:auto;}
.nav-label{font-size:9.5px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:rgba(200,153,42,0.42);padding:0 8px 9px;display:block;}

.cat-btn{
    display:flex;align-items:center;gap:9px;
    width:100%;padding:10px 13px;
    border:none;background:transparent;
    color:rgba(245,234,214,0.70);
    font-family:'Inter',sans-serif;font-size:13.5px;font-weight:500;
    text-align:left;border-radius:var(--r-sm);
    cursor:pointer;text-decoration:none;margin-bottom:2px;
    transition:all 0.25s var(--ease);
}
.cat-btn .ci{
    width:29px;height:29px;background:rgba(200,153,42,0.10);
    border-radius:7px;display:flex;align-items:center;justify-content:center;
    font-size:14px;flex-shrink:0;transition:background 0.22s;
}
.cat-btn:hover{background:rgba(200,153,42,0.13);color:#f5ead6;transform:translateX(3px);}
.cat-btn:hover .ci{background:rgba(200,153,42,0.22);}
.cat-btn.active{background:var(--gold);color:#2c1a0e;font-weight:700;box-shadow:0 4px 14px rgba(200,153,42,0.35);}
.cat-btn.active .ci{background:rgba(0,0,0,0.12);}

.sidebar-footer{padding:13px 16px 18px;border-top:1px solid rgba(200,153,42,0.16);}
.btn-add-side{
    display:flex;align-items:center;justify-content:center;gap:7px;
    width:100%;padding:11px 14px;
    background:linear-gradient(135deg,#c0392b,#96281b);
    color:#fff;border:none;border-radius:var(--r-md);
    font-family:'Inter',sans-serif;font-size:13px;font-weight:700;
    cursor:pointer;transition:all 0.25s var(--ease);
    box-shadow:0 4px 18px rgba(192,57,43,0.38);
}
.btn-add-side:hover{background:linear-gradient(135deg,#a93226,#7b231a);transform:translateY(-2px);box-shadow:0 8px 26px rgba(192,57,43,0.48);}

/* ──────────────────────────────────────────
   MAIN
────────────────────────────────────────── */
.main-content{margin-left:var(--sidebar-w);min-height:100vh;}

.topbar{
    background:#fff;border-bottom:1px solid rgba(0,0,0,0.07);
    padding:15px 30px;display:flex;align-items:center;gap:13px;
    position:sticky;top:0;z-index:100;
    box-shadow:0 2px 14px rgba(0,0,0,0.06);
}
.topbar-title{font-family:'Playfair Display',serif;font-size:18px;font-weight:800;color:var(--brown-dk);flex:1;}
.topbar-title small{display:block;font-family:'Inter',sans-serif;font-size:11px;font-weight:400;color:#9a9a9a;margin-top:1px;}

.search-wrap{position:relative;width:240px;}
.search-wrap input{
    width:100%;padding:8px 15px 8px 36px;
    border:1.5px solid #e0d5c5;border-radius:50px;
    font-size:13px;background:var(--bg);color:var(--text);outline:none;transition:border 0.2s;
}
.search-wrap input:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(200,153,42,0.11);}
.search-wrap .si{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#bbb;font-size:12px;}
.search-wrap .btn-srch{
    position:absolute;right:5px;top:50%;transform:translateY(-50%);
    background:var(--brown);color:#fff;border:none;border-radius:50px;
    padding:4px 12px;font-size:11.5px;cursor:pointer;transition:background 0.2s;
}
.search-wrap .btn-srch:hover{background:var(--brown-dk);}

.content-area{padding:28px 30px;}
.section-header{display:flex;align-items:center;gap:11px;margin-bottom:22px;}
.section-title{font-family:'Playfair Display',serif;font-size:23px;font-weight:800;color:var(--brown-dk);flex:1;}
.section-count{background:var(--parchment);color:var(--brown);font-size:12px;font-weight:700;padding:3px 11px;border-radius:50px;border:1px solid rgba(139,94,46,0.17);}

.btn-add-content{
    display:inline-flex;align-items:center;gap:6px;
    padding:8px 18px;
    background:linear-gradient(135deg,#c0392b,#96281b);
    color:#fff;border:none;border-radius:50px;
    font-size:13px;font-weight:700;cursor:pointer;
    transition:all 0.25s var(--ease);
    box-shadow:0 4px 13px rgba(192,57,43,0.28);
    text-decoration:none;white-space:nowrap;
}
.btn-add-content:hover{background:linear-gradient(135deg,#a93226,#7b231a);transform:translateY(-2px);box-shadow:0 7px 20px rgba(192,57,43,0.4);color:#fff;}

/* ──────────────────────────────────────────
   RECIPE CARDS
────────────────────────────────────────── */
.recipe-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:20px;}

.recipe-card{
    background:var(--card-bg);border-radius:var(--r-lg);overflow:hidden;
    box-shadow:var(--shadow);cursor:pointer;
    transition:transform 0.32s var(--spring),box-shadow 0.3s var(--ease);
    opacity:0;animation:fadeUp 0.42s var(--ease) forwards;
}
.recipe-card:hover{transform:translateY(-9px) scale(1.01);box-shadow:var(--hover-shadow);}
.recipe-card:hover .card-img img{transform:scale(1.1);}
.recipe-card:hover .card-overlay{opacity:1;}

@keyframes fadeUp{from{opacity:0;transform:translateY(22px);}to{opacity:1;transform:translateY(0);}}

.card-img{height:182px;overflow:hidden;position:relative;background:var(--parchment);}
.card-img img{width:100%;height:100%;object-fit:cover;transition:transform 0.5s var(--ease);display:block;}
.no-img{width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--parchment),#dfc99a);color:var(--brown-lt);gap:7px;}
.no-img i{font-size:36px;opacity:0.42;}
.no-img span{font-size:10.5px;opacity:0.42;}

.card-overlay{
    position:absolute;inset:0;
    background:linear-gradient(to top,rgba(44,26,14,0.78) 0%,transparent 55%);
    opacity:0;transition:opacity 0.3s var(--ease);
    display:flex;align-items:flex-end;padding:13px;
}
.view-btn{
    background:var(--gold);color:var(--ink);border:none;
    border-radius:50px;padding:6px 15px;font-size:12px;font-weight:700;cursor:pointer;
    transition:background 0.2s;
}
.view-btn:hover{background:var(--gold-lt);}

.cat-badge{
    position:absolute;top:10px;right:10px;
    padding:3px 9px;border-radius:50px;
    font-size:9.5px;font-weight:700;letter-spacing:0.4px;
    text-transform:uppercase;backdrop-filter:blur(4px);
}
.badge-sabaw{background:rgba(219,234,254,0.93);color:#1d4ed8;}
.badge-sarsado{background:rgba(252,231,243,0.93);color:#9d174d;}
.badge-ginataan{background:rgba(236,253,245,0.93);color:#065f46;}
.badge-prito{background:rgba(255,247,237,0.93);color:#c2410c;}
.badge-inihaw{background:rgba(255,243,205,0.93);color:#92400e;}
.badge-gulay{background:rgba(240,253,244,0.93);color:#15803d;}
.badge-sizzling{background:rgba(254,226,226,0.93);color:#991b1b;}

.card-body{padding:13px 15px 10px;}
.card-name{
    font-family:'Playfair Display',serif;font-size:15.5px;font-weight:700;
    color:var(--brown-dk);margin-bottom:5px;line-height:1.3;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.card-meta{font-size:11px;color:#aaa;display:flex;align-items:center;gap:5px;}

.card-actions{display:flex;gap:5px;padding:0 15px 13px;}
.btn-card{
    flex:1;padding:6px 0;border-radius:var(--r-sm);
    font-size:11.5px;font-weight:600;cursor:pointer;
    transition:all 0.22s var(--ease);
    display:flex;align-items:center;justify-content:center;gap:5px;
    border:1.5px solid;background:transparent;
}
.btn-card-edit{border-color:var(--brown-lt);color:var(--brown);}
.btn-card-edit:hover{background:var(--brown);color:#fff;border-color:var(--brown);}
.btn-card-del{border-color:#fca5a5;color:var(--red);}
.btn-card-del:hover{background:var(--red);color:#fff;border-color:var(--red);}

.empty-state{grid-column:1/-1;text-align:center;padding:80px 20px;}
.empty-state i{font-size:58px;color:var(--brown-lt);opacity:0.27;display:block;margin-bottom:15px;}
.empty-state h4{font-family:'Playfair Display',serif;color:var(--brown-dk);margin-bottom:7px;}
.empty-state p{color:#bbb;font-size:13.5px;}

/* ──────────────────────────────────────────
   FLASH TOAST
────────────────────────────────────────── */
.flash-toast{
    position:fixed;top:16px;right:18px;z-index:9999;
    min-width:255px;border-radius:var(--r-md);
    box-shadow:0 8px 28px rgba(0,0,0,0.17);font-size:13.5px;
    animation:toastIn 0.45s var(--spring) forwards;
}
@keyframes toastIn{from{opacity:0;transform:translateX(55px);}to{opacity:1;transform:translateX(0);}}

/* ──────────────────────────────────────────
   VIEW MODAL  —  fixed image, scroll right
────────────────────────────────────────── */
#recipeModal .modal-dialog{max-width:900px;}
#recipeModal .modal-content{
    border:none;border-radius:var(--r-lg);overflow:hidden;
    box-shadow:0 28px 90px rgba(0,0,0,0.28);
    max-height:90vh;display:flex;flex-direction:column;
}
#recipeModal .modal-header{
    background:linear-gradient(135deg,#2e1504 0%,#6b3010 100%);
    border:none;padding:19px 24px 15px;flex-shrink:0;
}
.vm-title{
    font-family:'Playfair Display',serif;font-size:20px;font-weight:900;
    color:#f5ead6;margin:0 0 6px;line-height:1.2;
}
.vm-badge{
    display:inline-block;font-size:9.5px;font-weight:700;
    letter-spacing:0.8px;text-transform:uppercase;
    padding:3px 11px;border-radius:50px;
    background:rgba(200,153,42,0.22);color:#f5ead6;
    border:1px solid rgba(200,153,42,0.38);
}

/* modal-body: flex, no overflow — children own their scroll */
#recipeModal .modal-body{padding:0;flex:1;overflow:hidden;display:flex;}

/* 2-column grid */
.vm-grid{display:grid;grid-template-columns:290px 1fr;width:100%;overflow:hidden;}

/* LEFT — image, truly fixed */
.vm-left{
    background:#f0e6cc;border-right:1px solid #ddd0b0;
    display:flex;align-items:center;justify-content:center;
    padding:22px 16px;overflow:hidden;position:relative;
}
.vm-img-wrap{width:100%;border-radius:var(--r-md);overflow:hidden;}
.vm-img-wrap img{
    width:100%;max-height:370px;object-fit:cover;display:block;
    border-radius:var(--r-md);
    transition:transform 0.45s var(--ease);
}
.vm-img-wrap:hover img{transform:scale(1.06);}
.vm-img-ph{
    width:100%;height:220px;
    background:linear-gradient(135deg,#e8d5b0,#d0b87a);
    border-radius:var(--r-md);
    display:flex;align-items:center;justify-content:center;
}
.vm-img-ph i{font-size:52px;color:var(--brown);opacity:0.2;}

/* RIGHT — scrollable only */
.vm-right{
    padding:22px 26px;
    overflow-y:auto;overflow-x:hidden;
    /* fills the remaining height between modal header and footer */
    max-height:calc(90vh - 128px);
}
.vm-section{margin-bottom:22px;}
.vm-section:last-child{margin-bottom:0;}
.vm-sec-label{
    font-family:'Playfair Display',serif;font-size:12px;font-weight:700;
    color:var(--brown);text-transform:uppercase;letter-spacing:1.2px;
    margin-bottom:11px;padding-bottom:8px;border-bottom:2px solid #f0e4cc;
    display:flex;align-items:center;gap:6px;
}
.vm-sec-label .dot{color:var(--gold);font-size:8.5px;}

.ing-list{list-style:none;padding:0;margin:0;}
.ing-list li{
    display:flex;align-items:flex-start;gap:9px;
    padding:7px 0;font-size:13.5px;line-height:1.5;
    color:var(--text);border-bottom:1px solid #f5f0e8;
}
.ing-list li:last-child{border-bottom:none;}
.ing-list li::before{
    content:'';display:block;width:6px;height:6px;border-radius:50%;
    background:var(--gold);flex-shrink:0;margin-top:5px;
}

.proc-list{list-style:none;padding:0;margin:0;counter-reset:sc;}
.proc-list li{
    display:flex;align-items:flex-start;gap:11px;
    padding:9px 0;font-size:13.5px;line-height:1.6;
    color:var(--text);border-bottom:1px solid #f5f0e8;counter-increment:sc;
}
.proc-list li:last-child{border-bottom:none;}
.proc-list li::before{
    content:counter(sc);
    display:flex;align-items:center;justify-content:center;
    min-width:25px;height:25px;border-radius:50%;
    background:var(--brown-dk);color:#f5ead6;
    font-size:11px;font-weight:700;flex-shrink:0;margin-top:2px;
}

#recipeModal .modal-footer{background:var(--cream);border-top:1px solid #e8dcc8;padding:10px 22px;flex-shrink:0;}

.modal-spinner{
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    min-height:300px;gap:13px;color:#aaa;font-size:13.5px;width:100%;
}

/* ──────────────────────────────────────────
   ADD / EDIT MODALS
────────────────────────────────────────── */
.form-modal .modal-content{border:none;border-radius:var(--r-lg);overflow:hidden;box-shadow:0 24px 80px rgba(0,0,0,0.22);}
.form-modal .modal-header{padding:19px 24px 15px;border-bottom:1px solid rgba(0,0,0,0.06);}
.add-hd{background:linear-gradient(135deg,#2e1504,#6b3010);}
.edit-hd{background:linear-gradient(135deg,#1a3a1a,#2d6b2d);}
.form-modal .modal-title{font-family:'Playfair Display',serif;font-size:18px;font-weight:800;color:#f5ead6;}
.form-modal .modal-body{padding:22px 26px;}
.form-modal .modal-footer{padding:11px 22px;background:var(--cream);border-top:1px solid #e8dcc8;}

.fm-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--brown);margin-bottom:5px;display:block;}
.fm-ctrl{
    width:100%;padding:9px 13px;border:1.5px solid #ddd;border-radius:var(--r-sm);
    font-size:13px;font-family:'Inter',sans-serif;color:var(--text);background:#fff;
    transition:border 0.2s;outline:none;
}
.fm-ctrl:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(200,153,42,0.11);}
select.fm-ctrl{cursor:pointer;}
textarea.fm-ctrl{resize:vertical;min-height:96px;}

.img-zone{
    border:2px dashed #ddd;border-radius:var(--r-md);padding:16px;
    text-align:center;cursor:pointer;background:var(--cream);transition:border-color 0.2s;
}
.img-zone:hover{border-color:var(--gold);}
.img-zone .zi{font-size:30px;color:var(--brown-lt);opacity:0.42;}
.img-zone p{font-size:12px;color:#aaa;margin:5px 0 0;}
.img-prev{max-height:150px;border-radius:var(--r-sm);display:none;margin:7px auto 0;}

.btn-save-add{
    background:linear-gradient(135deg,#2e1504,#6b3010);color:#fff;border:none;
    padding:10px 26px;border-radius:50px;font-size:13.5px;font-weight:700;
    cursor:pointer;transition:all 0.25s var(--ease);
    box-shadow:0 4px 15px rgba(91,57,30,0.3);
}
.btn-save-add:hover{transform:translateY(-2px);box-shadow:0 8px 22px rgba(91,57,30,0.4);}
.btn-save-edit{
    background:linear-gradient(135deg,#1a3a1a,#2d6b2d);color:#fff;border:none;
    padding:10px 26px;border-radius:50px;font-size:13.5px;font-weight:700;
    cursor:pointer;transition:all 0.25s var(--ease);
    box-shadow:0 4px 15px rgba(30,80,30,0.3);
}
.btn-save-edit:hover{transform:translateY(-2px);box-shadow:0 8px 22px rgba(30,80,30,0.4);}
.btn-cancel{
    background:transparent;border:1.5px solid #ddd;color:#777;
    padding:9px 20px;border-radius:50px;font-size:13px;font-weight:600;
    cursor:pointer;transition:all 0.22s var(--ease);
}
.btn-cancel:hover{border-color:#bbb;background:#f5f5f5;color:#444;}

/* ──────────────────────────────────────────
   DELETE MODAL
────────────────────────────────────────── */
#deleteModal .modal-content{border:none;border-radius:var(--r-lg);overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.22);}
#deleteModal .modal-header{background:linear-gradient(135deg,#7f1d1d,#991b1b);border:none;padding:17px 22px 13px;}
#deleteModal .modal-title{font-family:'Playfair Display',serif;font-size:16.5px;font-weight:800;color:#fff;}
#deleteModal .modal-body{padding:23px 26px;}
.del-dish-name{font-family:'Playfair Display',serif;font-weight:700;color:var(--brown-dk);font-size:15.5px;}
#deleteModal .modal-footer{background:var(--cream);border-top:1px solid #e8dcc8;padding:11px 22px;gap:10px;}
.btn-yes-del{
    background:var(--red);color:#fff;border:none;
    padding:9px 24px;border-radius:50px;font-size:13.5px;font-weight:700;
    cursor:pointer;transition:all 0.22s var(--ease);
    box-shadow:0 4px 13px rgba(192,57,43,0.3);
}
.btn-yes-del:hover{background:#a93226;transform:translateY(-1px);}
.btn-no-del{
    background:transparent;color:#666;border:1.5px solid #ddd;
    padding:8px 20px;border-radius:50px;font-size:13px;font-weight:600;
    cursor:pointer;transition:all 0.22s var(--ease);
}
.btn-no-del:hover{border-color:#bbb;background:#f5f5f5;}

/* ──────────────────────────────────────────
   RESPONSIVE
────────────────────────────────────────── */
@media(max-width:900px){
    .vm-grid{grid-template-columns:1fr;}
    .vm-left{border-right:none;border-bottom:1px solid #ddd0b0;padding:14px;max-height:200px;}
    .vm-right{max-height:48vh;}
    #recipeModal .modal-dialog{max-width:96vw;}
}
@media(max-width:768px){
    .sidebar{transform:translateX(-100%);}
    .main-content{margin-left:0;}
    .content-area{padding:16px;}
    .topbar{padding:12px 14px;}
    .search-wrap{width:170px;}
}
</style>
</head>
<body>

<?php /* flash toasts */
if (isset($_GET['added'])): ?>
<div class="flash-toast alert alert-success d-flex align-items-center gap-2 py-2 px-3 mb-0">
    <i class="fas fa-check-circle"></i> Recipe na-add na!
</div>
<?php elseif (isset($_GET['edited'])): ?>
<div class="flash-toast alert alert-info d-flex align-items-center gap-2 py-2 px-3 mb-0">
    <i class="fas fa-pen-to-square"></i> Recipe na-update na!
</div>
<?php elseif (isset($_GET['deleted'])): ?>
<div class="flash-toast alert alert-warning d-flex align-items-center gap-2 py-2 px-3 mb-0">
    <i class="fas fa-trash"></i> Recipe na-delete na.
</div>
<?php endif; ?>

<!-- ════════════════════ SIDEBAR ════════════════════ -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <span class="brand-logo">🍳</span>
        <div class="brand-title">Filipino <span>Ulam</span></div>
        <span class="brand-sub">Ang Lutong Pilipino</span>
    </div>
    <nav class="sidebar-nav">
        <span class="nav-label">Kategorya</span>
        <?php
        $icons=['All'=>'🍽️','Sabaw'=>'🍲','Sarsado'=>'🥘','Ginataan'=>'🥥',
                'Prito'=>'🍳','Inihaw'=>'🔥','Gulay'=>'🥦','Sizzling'=>'⚡'];
        foreach(array_merge(['All'],$categories) as $cat):
            $a=($active_cat===$cat)?'active':'';
            $icon=$icons[$cat]??'🍴';
            $url=($cat==='All')?'home.php':'home.php?cat='.urlencode($cat);
            if($search)$url.='&q='.urlencode($search);
        ?>
        <a href="<?=$url?>" class="cat-btn <?=$a?>">
            <span class="ci"><?=$icon?></span>
            <?=htmlspecialchars($cat)?>
        </a>
        <?php endforeach;?>
    </nav>
    <div class="sidebar-footer">
        <button class="btn-add-side" onclick="openAddModal()">
            <i class="fas fa-plus"></i> Magdagdag ng Ulam
        </button>
    </div>
</aside>

<!-- ════════════════════ MAIN ════════════════════ -->
<main class="main-content">
    <div class="topbar">
        <div class="topbar-title">
            <?=$active_cat==='All'?'Lahat ng Ulam':htmlspecialchars($active_cat)?>
            <small><?=count($recipes)?> recipe<?=count($recipes)!==1?'s':''?><?=$search?' na nahanap':''?></small>
        </div>
        <form method="GET" action="home.php" style="display:flex;gap:8px;align-items:center;">
            <?php if($active_cat!=='All'):?><input type="hidden" name="cat" value="<?=htmlspecialchars($active_cat)?>"> <?php endif;?>
            <div class="search-wrap">
                <i class="fas fa-search si"></i>
                <input type="text" name="q" placeholder="Hanapin ang ulam…" value="<?=htmlspecialchars($search)?>">
                <button type="submit" class="btn-srch">Hanapin</button>
            </div>
        </form>
        <?php if($search):?>
        <a href="home.php<?=$active_cat!=='All'?'?cat='.urlencode($active_cat):''?>"
           style="text-decoration:none;font-size:12px;color:#888;padding:5px 13px;border:1.5px solid #ddd;border-radius:50px;">✕ I-clear</a>
        <?php endif;?>
    </div>

    <div class="content-area">
        <div class="section-header">
            <div class="section-title">
                <?=$search?'Resulta: "'.htmlspecialchars($search).'"':($active_cat==='All'?'Lahat ng Ulam':htmlspecialchars($active_cat))?>
            </div>
            <span class="section-count"><?=count($recipes)?></span>
            <?php /* ── Add button visible ONLY in "All" category, no search active ── */?>
            <?php if($active_cat==='All'&&!$search):?>
            <button class="btn-add-content" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Magdagdag ng Ulam
            </button>
            <?php endif;?>
        </div>

        <?php if($flash)echo $flash;?>

        <div class="recipe-grid">
        <?php if(empty($recipes)):?>
            <div class="empty-state">
                <i class="fas fa-utensils"></i>
                <h4>Walang Recipe</h4>
                <p>Wala pang recipe dito. Mag-click ng "Magdagdag ng Ulam" para magsimula!</p>
                <button class="btn-add-content mt-3" onclick="openAddModal()"><i class="fas fa-plus"></i> Magdagdag</button>
            </div>
        <?php else:
        $bmap=['sabaw'=>'badge-sabaw','sarsado'=>'badge-sarsado','ginataan'=>'badge-ginataan',
               'prito'=>'badge-prito','inihaw'=>'badge-inihaw','gulay'=>'badge-gulay','sizzling'=>'badge-sizzling'];
        foreach($recipes as $i=>$r):
            $bc=$bmap[strtolower($r['category'])]??'badge-prito';
            $dl=min(($i%8)*70,500);
        ?>
        <div class="recipe-card" style="animation-delay:<?=$dl?>ms" onclick="viewRecipe(<?=$r['id']?>)">
            <div class="card-img">
                <?php if($r['image']&&file_exists('uploads/'.$r['image'])):?>
                <img src="uploads/<?=htmlspecialchars($r['image'])?>" alt="<?=htmlspecialchars($r['name'])?>">
                <?php else:?><div class="no-img"><i class="fas fa-utensils"></i><span>Walang larawan</span></div><?php endif;?>
                <span class="cat-badge <?=$bc?>"><?=htmlspecialchars($r['category'])?></span>
                <div class="card-overlay"><button class="view-btn"><i class="fas fa-eye me-1"></i> Tingnan</button></div>
            </div>
            <div class="card-body">
                <div class="card-name" title="<?=htmlspecialchars($r['name'])?>"><?=htmlspecialchars($r['name'])?></div>
                <div class="card-meta"><i class="fas fa-clock" style="color:var(--brown-lt)"></i><?=date('M d, Y',strtotime($r['created_at']))?></div>
            </div>
            <div class="card-actions">
                <button class="btn-card btn-card-edit"
                    onclick="event.stopPropagation();openEditModal(
                        <?=$r['id']?>,
                        <?=json_encode($r['name'])?>,
                        <?=json_encode($r['category'])?>,
                        <?=json_encode($r['ingredients'])?>,
                        <?=json_encode($r['procedure_steps'])?>,
                        <?=json_encode($r['image']??'')?>
                    )"><i class="fas fa-pen"></i> I-edit</button>
                <button class="btn-card btn-card-del"
                    onclick="event.stopPropagation();confirmDelete(<?=$r['id']?>,<?=json_encode($r['name'])?>)">
                    <i class="fas fa-trash"></i> Tanggalin</button>
            </div>
        </div>
        <?php endforeach;endif;?>
        </div>
    </div>
</main>

<!-- ════════════════════ VIEW MODAL ════════════════════ -->
<div class="modal fade" id="recipeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" id="vmHeader">
        <div>
          <div class="vm-title" id="recipeTitle">Nilo-load…</div>
          <span class="vm-badge" id="recipeBadge"></span>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewModalBody">
        <div class="modal-spinner">
          <div class="spinner-border" style="color:var(--gold);width:2.3rem;height:2.3rem;" role="status"></div>
          <span>Nilo-load ang recipe…</span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" data-bs-dismiss="modal">Isara</button>
      </div>
    </div>
  </div>
</div>

<!-- ════════════════════ ADD MODAL ════════════════════ -->
<div class="modal fade form-modal" id="addModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:570px;">
    <div class="modal-content">
      <div class="modal-header add-hd">
        <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Magdagdag ng Bagong Ulam</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data" id="addForm">
        <input type="hidden" name="_action" value="add">
        <div class="modal-body">
          <?php if($flash&&($_POST['_action']??'')==='add')echo $flash;?>
          <div class="mb-3">
            <label class="fm-label">Pangalan ng Ulam <span style="color:var(--red)">*</span></label>
            <input type="text" name="name" class="fm-ctrl" placeholder="hal. Sinigang na Baboy…"
                   value="<?=htmlspecialchars(($_POST['_action']??'')==='add'?($_POST['name']??''):'')?>" required>
          </div>
          <div class="mb-3">
            <label class="fm-label">Kategorya <span style="color:var(--red)">*</span></label>
            <select name="category" class="fm-ctrl" required>
              <option value="">— Piliin ang Kategorya —</option>
              <?php foreach($categories as $cat):?>
              <option value="<?=$cat?>" <?=(($_POST['category']??'')===$cat&&($_POST['_action']??'')==='add')?'selected':''?>><?=$cat?></option>
              <?php endforeach;?>
            </select>
          </div>
          <div class="mb-3">
            <label class="fm-label">Larawan <small style="color:#aaa;font-weight:400;text-transform:none">(opsyonal)</small></label>
            <div class="img-zone" onclick="document.getElementById('aImg').click()">
              <div class="zi"><i class="fas fa-camera"></i></div>
              <p>I-click para mag-upload (JPG, PNG, WEBP)</p>
              <img id="aPrev" class="img-prev" src="" alt="">
            </div>
            <input type="file" name="image" id="aImg" accept=".jpg,.jpeg,.png,.webp" style="display:none">
          </div>
          <div class="mb-3">
            <label class="fm-label">Mga Sangkap <span style="color:var(--red)">*</span></label>
            <textarea name="ingredients" class="fm-ctrl"
                      placeholder="Isang sangkap bawat linya:&#10;500g baboy&#10;3 butil ng bawang" required><?=htmlspecialchars(($_POST['_action']??'')==='add'?($_POST['ingredients']??''):'')?></textarea>
            <div style="font-size:11px;color:#aaa;margin-top:3px;">Isang sangkap bawat linya</div>
          </div>
          <div class="mb-1">
            <label class="fm-label">Paraan ng Pagluto <span style="color:var(--red)">*</span></label>
            <textarea name="procedure_steps" class="fm-ctrl" style="min-height:110px;"
                      placeholder="Isang hakbang bawat linya:&#10;Igisa ang bawang.&#10;Ilagay ang karne." required><?=htmlspecialchars(($_POST['_action']??'')==='add'?($_POST['procedure_steps']??''):'')?></textarea>
            <div style="font-size:11px;color:#aaa;margin-top:3px;">Isang hakbang bawat linya</div>
          </div>
        </div>
        <div class="modal-footer" style="gap:9px;">
          <button type="submit" class="btn-save-add"><i class="fas fa-save me-1"></i> I-save ang Recipe</button>
          <button type="button" class="btn-cancel" data-bs-dismiss="modal">Kanselahin</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ════════════════════ EDIT MODAL ════════════════════ -->
<div class="modal fade form-modal" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:570px;">
    <div class="modal-content">
      <div class="modal-header edit-hd">
        <h5 class="modal-title"><i class="fas fa-pen me-2"></i>I-edit ang Recipe</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data" id="editForm">
        <input type="hidden" name="_action" value="edit">
        <input type="hidden" name="id" id="eId">
        <input type="hidden" name="current_image" id="eCurImg">
        <div class="modal-body">
          <div class="mb-3">
            <label class="fm-label">Pangalan ng Ulam <span style="color:var(--red)">*</span></label>
            <input type="text" name="name" id="eName" class="fm-ctrl" required>
          </div>
          <div class="mb-3">
            <label class="fm-label">Kategorya <span style="color:var(--red)">*</span></label>
            <select name="category" id="eCat" class="fm-ctrl" required>
              <option value="">— Piliin ang Kategorya —</option>
              <?php foreach($categories as $cat):?><option value="<?=$cat?>"><?=$cat?></option><?php endforeach;?>
            </select>
          </div>
          <div class="mb-3">
            <label class="fm-label">Larawan <small style="color:#aaa;font-weight:400;text-transform:none">(opsyonal — para palitan)</small></label>
            <div class="img-zone" onclick="document.getElementById('eImg').click()">
              <img id="ePrev" class="img-prev" src="" alt="">
              <div id="ePh"><div class="zi"><i class="fas fa-camera"></i></div><p>I-click para palitan ang larawan</p></div>
            </div>
            <input type="file" name="image" id="eImg" accept=".jpg,.jpeg,.png,.webp" style="display:none">
          </div>
          <div class="mb-3">
            <label class="fm-label">Mga Sangkap <span style="color:var(--red)">*</span></label>
            <textarea name="ingredients" id="eIng" class="fm-ctrl" required></textarea>
            <div style="font-size:11px;color:#aaa;margin-top:3px;">Isang sangkap bawat linya</div>
          </div>
          <div class="mb-1">
            <label class="fm-label">Paraan ng Pagluto <span style="color:var(--red)">*</span></label>
            <textarea name="procedure_steps" id="eProc" class="fm-ctrl" style="min-height:110px;" required></textarea>
            <div style="font-size:11px;color:#aaa;margin-top:3px;">Isang hakbang bawat linya</div>
          </div>
        </div>
        <div class="modal-footer" style="gap:9px;">
          <button type="submit" class="btn-save-edit"><i class="fas fa-save me-1"></i> I-update ang Recipe</button>
          <button type="button" class="btn-cancel" data-bs-dismiss="modal">Kanselahin</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ════════════════════ DELETE MODAL ════════════════════ -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:410px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-triangle-exclamation me-2"></i>Kumpirmahin ang Pagtanggal</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px 26px;">
        <p style="font-size:14.5px;color:var(--text);margin-bottom:10px;">
            Sigurado ka bang gusto mong i-delete ang dish na ito?
        </p>
        <div style="background:var(--parchment);border-radius:var(--r-sm);padding:11px 15px;border-left:4px solid var(--red);">
            <span class="del-dish-name" id="delDishName"></span>
        </div>
        <p style="font-size:12px;color:#aaa;margin-top:11px;margin-bottom:0;">
            <i class="fas fa-info-circle me-1"></i>Hindi na ito mababawi pagkatapos matanggal.
        </p>
      </div>
      <div class="modal-footer" style="gap:9px;">
        <button class="btn-yes-del" id="btnConfirmDel"><i class="fas fa-trash me-1"></i> Oo, I-delete</button>
        <button class="btn-no-del" data-bs-dismiss="modal">Hindi, Huwag</button>
      </div>
    </div>
  </div>
</div>

<!-- ════════════════════ SCRIPTS ════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ── Bootstrap instances ── */
const recipeModal = new bootstrap.Modal(document.getElementById('recipeModal'));
const addModal    = new bootstrap.Modal(document.getElementById('addModal'));
const editModal   = new bootstrap.Modal(document.getElementById('editModal'));
const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

/* ── Auto-dismiss toast ── */
const toast=document.querySelector('.flash-toast');
if(toast)setTimeout(()=>{toast.style.transition='opacity 0.6s';toast.style.opacity='0';setTimeout(()=>toast.remove(),600);},3500);

/* ═══════════════════════════════════
   VIEW RECIPE
═══════════════════════════════════ */
function viewRecipe(id){
    document.getElementById('recipeTitle').textContent='Nilo-load…';
    document.getElementById('recipeBadge').textContent='';
    document.getElementById('viewModalBody').innerHTML=
        '<div class="modal-spinner"><div class="spinner-border" style="color:var(--gold);width:2.3rem;height:2.3rem;" role="status"></div><span>Nilo-load ang recipe…</span></div>';
    recipeModal.show();

    fetch('get_recipe.php?id='+id)
        .then(r=>r.json())
        .then(data=>{
            if(!data||data.error){
                document.getElementById('viewModalBody').innerHTML='<p class="text-danger text-center py-5 w-100">Hindi mahanap ang recipe.</p>';
                return;
            }
            document.getElementById('recipeTitle').textContent=data.name;
            document.getElementById('recipeBadge').textContent=data.category;

            const imgHtml=data.image
                ?`<div class="vm-img-wrap" id="recipeImage"><img src="uploads/${esc(data.image)}" alt="${esc(data.name)}"></div>`
                :`<div class="vm-img-wrap" id="recipeImage"><div class="vm-img-ph"><i class="fas fa-utensils"></i></div></div>`;

            const ings=data.ingredients.split('\n').filter(l=>l.trim());
            const ingHtml=ings.map(l=>`<li>${esc(l.trim().replace(/^[-•*]\s*/,''))}</li>`).join('')||'<li>Walang sangkap na nakalista.</li>';

            const steps=data.procedure_steps.split('\n').filter(l=>l.trim());
            const procHtml=steps.map(l=>`<li>${esc(l.trim().replace(/^\d+[.)]\s*/,''))}</li>`).join('')||'<li>Walang hakbang na nakalista.</li>';

            document.getElementById('viewModalBody').innerHTML=`
                <div class="vm-grid">
                    <div class="vm-left">${imgHtml}</div>
                    <div class="vm-right" id="recipeScrollRight">
                        <div class="vm-section">
                            <div class="vm-sec-label"><span class="dot">&#9670;</span> Mga Sangkap</div>
                            <ul class="ing-list" id="recipeIngredients">${ingHtml}</ul>
                        </div>
                        <div class="vm-section">
                            <div class="vm-sec-label"><span class="dot">&#9670;</span> Paraan ng Pagluto</div>
                            <ol class="proc-list" id="recipeProcedure">${procHtml}</ol>
                        </div>
                    </div>
                </div>`;
        })
        .catch(()=>{
            document.getElementById('viewModalBody').innerHTML='<p class="text-danger text-center py-5 w-100">Nagkaroon ng error. Subukan ulit.</p>';
        });
}

/* ═══════════════════════════════════
   ADD MODAL
═══════════════════════════════════ */
function openAddModal(){
    document.getElementById('addForm').reset();
    const p=document.getElementById('aPrev');
    p.style.display='none';p.src='';
    addModal.show();
}
document.getElementById('aImg').addEventListener('change',function(){
    imgPreview(this,'aPrev',null);
});

/* ═══════════════════════════════════
   EDIT MODAL
═══════════════════════════════════ */
function openEditModal(id,name,category,ingredients,procedure,image){
    document.getElementById('eId').value   =id;
    document.getElementById('eName').value =name;
    document.getElementById('eIng').value  =ingredients;
    document.getElementById('eProc').value =procedure;
    document.getElementById('eCurImg').value=image;

    const sel=document.getElementById('eCat');
    for(let o of sel.options) o.selected=(o.value===category);

    const prev=document.getElementById('ePrev');
    const ph  =document.getElementById('ePh');
    if(image){
        prev.src='uploads/'+esc(image);
        prev.style.display='block';
        ph.style.display='none';
    } else {
        prev.src='';prev.style.display='none';
        ph.style.display='block';
    }
    document.getElementById('eImg').value='';
    editModal.show();
}
document.getElementById('eImg').addEventListener('change',function(){
    imgPreview(this,'ePrev','ePh');
});

/* ═══════════════════════════════════
   DELETE MODAL
═══════════════════════════════════ */
function confirmDelete(id,name){
    document.getElementById('delDishName').textContent=name;
    document.getElementById('btnConfirmDel').onclick=function(){
        window.location.href='home.php?delete='+id;
    };
    deleteModal.show();
}

/* ═══════════════════════════════════
   HELPERS
═══════════════════════════════════ */
function imgPreview(input,prevId,phId){
    if(!input.files||!input.files[0])return;
    const r=new FileReader();
    r.onload=e=>{
        const img=document.getElementById(prevId);
        img.src=e.target.result;img.style.display='block';
        if(phId){const ph=document.getElementById(phId);if(ph)ph.style.display='none';}
    };
    r.readAsDataURL(input.files[0]);
}
function esc(str){const d=document.createElement('div');d.textContent=str;return d.innerHTML;}

/* re-open modals on POST error */
<?php if($flash&&($_POST['_action']??'')==='add'):?>
document.addEventListener('DOMContentLoaded',()=>addModal.show());
<?php elseif($flash&&($_POST['_action']??'')==='edit'):?>
document.addEventListener('DOMContentLoaded',()=>editModal.show());
<?php endif;?>
</script>
</body>
</html>