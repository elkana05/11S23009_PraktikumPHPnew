<?php
// Mulai session jika belum ada.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Aktifkan pelaporan error penuh untuk lingkungan development.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sertakan controller
require_once __DIR__ . '/controllers/TodoController.php';

$controller = new TodoController();

// Ambil action dari URL, default ke 'index'
$action = $_GET['action'] ?? 'index';

// Panggil metode yang sesuai di controller
if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    // Jika action tidak ditemukan, tampilkan halaman 404 atau kembali ke index.
    http_response_code(404);
    $controller->index();
}