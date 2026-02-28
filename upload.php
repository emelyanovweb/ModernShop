<?php
// upload.php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit;
}

if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($_FILES['image']['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Недопустимый формат файла']);
        exit;
    }
    
    if ($_FILES['image']['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'Файл слишком большой (макс. 5MB)']);
        exit;
    }
    
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = 'setting_' . time() . '_' . uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . $filename;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
        echo json_encode(['success' => true, 'filename' => $filename]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка при сохранении файла']);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Файл не загружен']);
?>