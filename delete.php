<?php
require 'db.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verifyCsrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit('Invalid student ID.');
}

$stmt = $pdo->prepare(
    "SELECT file_path FROM student_files WHERE student_id = ?"
);
$stmt->execute([$id]);
$files = $stmt->fetchAll();

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);

    $pdo->commit();

    $root = realpath(__DIR__ . '/uploads');

    foreach ($files as $file) {
        $path = realpath(__DIR__ . '/' . $file['file_path']);

        if (
            $root &&
            $path &&
            str_starts_with($path, $root . DIRECTORY_SEPARATOR) &&
            is_file($path)
        ) {
            unlink($path);
        }
    }

    header('Location: index.php');
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());
    http_response_code(500);
    exit('Unable to delete student.');
}