<?php
// ============================================================
//  get_recipe.php — AJAX Endpoint
//  Filipino Ulam Recipe System
//
//  Called by: home.php → viewRecipe(id) via fetch()
//  Returns:   JSON object of one recipe row
//  Usage:     fetch('get_recipe.php?id=5')
// ============================================================

require_once 'db.php';

header('Content-Type: application/json');

// Validate the id parameter
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(['error' => true, 'message' => 'Invalid or missing ID.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $recipe = $stmt->fetch();

    if (!$recipe) {
        echo json_encode(['error' => true, 'message' => 'Recipe not found.']);
        exit;
    }

    // Return the full recipe row as JSON
    echo json_encode($recipe);

} catch (\PDOException $e) {
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}