<?php
// ============================================================
//  db.php — Database Connection
//  Filipino Ulam Recipe System
//  Uses PDO with proper error handling.
//  Place this file in the same folder as all other PHP files.
// ============================================================

$host    = 'localhost';
$db      = 'ulam_recipes';
$user    = 'root';
$pass    = '';           // Default XAMPP password is blank
$charset = 'utf8mb4';

$dsn     = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Show a friendly setup message instead of a raw PHP error
    die('
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Database Error</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light p-5">
        <div class="card border-warning shadow-sm mx-auto" style="max-width:640px;">
            <div class="card-header bg-warning text-dark fw-bold">
                ⚠️ Database Connection Failed
            </div>
            <div class="card-body">
                <p class="mb-2">Make sure <strong>XAMPP</strong> is running and the database exists.</p>
                <p class="mb-3">Run this SQL in <strong>phpMyAdmin → SQL tab</strong> first:</p>
                <pre class="bg-dark text-success p-3 rounded small">
CREATE DATABASE IF NOT EXISTS ulam_recipes
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ulam_recipes;

CREATE TABLE IF NOT EXISTS recipes (
    id              INT           NOT NULL AUTO_INCREMENT,
    name            VARCHAR(100)  NOT NULL,
    category        VARCHAR(50)   NOT NULL,
    ingredients     TEXT          NOT NULL,
    procedure_steps TEXT          NOT NULL,
    image           VARCHAR(255)  DEFAULT NULL,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;</pre>
                <hr>
                <p class="text-danger small mb-0">
                    <strong>PHP Error:</strong> ' . htmlspecialchars($e->getMessage()) . '
                </p>
            </div>
        </div>
    </body>
    </html>
    ');
}