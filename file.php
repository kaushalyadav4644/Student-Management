<?php
require 'db.php';
requireAdmin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit('Invalid file ID.');
}

$stmt = $pdo->prepare(
    "SELECT file_path, original_name, file_type
     FROM student_files WHERE id = ?"
);
$stmt->execute([$id]);
$file = $stmt->fetch();

if (!$file) {
    http_response_code(404);
    exit('File not found.');
}

$uploadRoot = realpath(__DIR__ . '/uploads');
$path = realpath(__DIR__ . '/' . $file['file_path']);

if (
    !$uploadRoot ||
    !$path ||
    !str_starts_with($path, $uploadRoot . DIRECTORY_SEPARATOR) ||
    !is_file($path)
) {
    http_response_code(404);
    exit('File is missing.');
}

$allowedTypes = [
    'image/jpeg',
    'image/png',
    'application/pdf'
];

if (!in_array($file['file_type'], $allowedTypes, true)) {
    http_response_code(403);
    exit('File type not permitted.');
}

$mode = ($_GET['mode'] ?? '') === 'view'
    ? 'inline'
    : 'attachment';

$name = basename($file['original_name']);
$name = str_replace(["\r", "\n", '"'], '', $name);

header('X-Content-Type-Options: nosniff');
header('Content-Type: ' . $file['file_type']);
header('Content-Length: ' . filesize($path));
header('Content-Disposition: ' . $mode . '; filename="' . $name . '"');

readfile($path);
exit;