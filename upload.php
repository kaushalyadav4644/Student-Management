<?php

function saveStudentFile(
    PDO $pdo,
    int $studentId,
    array $file,
    string $category
) {
    if (!in_array($category, ['profile', 'document'], true)) {
        throw new RuntimeException("Invalid file category.");
    }

    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($error === UPLOAD_ERR_NO_FILE) {
        return;
    }

    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException("File upload failed.");
    }

    $tmp = $file['tmp_name'] ?? '';

    if (!is_uploaded_file($tmp)) {
        throw new RuntimeException("Invalid uploaded file.");
    }

    $size = (int)($file['size'] ?? 0);
    $maxSize = 2 * 1024 * 1024;

    if ($size <= 0 || $size > $maxSize) {
        throw new RuntimeException("Maximum file size is 2 MB.");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);

    if ($category === 'profile') {
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png'
        ];
    } else {
        $allowed = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png'
        ];
    }

    if (!isset($allowed[$mime])) {
        throw new RuntimeException("File type is not allowed.");
    }

    $uploadDir = __DIR__ . '/uploads';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!is_writable($uploadDir)) {
        throw new RuntimeException("Uploads folder is not writable.");
    }

    $storedName = bin2hex(random_bytes(20))
        . '.' . $allowed[$mime];

    $destination = $uploadDir . '/' . $storedName;

    // Create exclusively so an existing file cannot be overwritten.
    $handle = @fopen($destination, 'x');

    if ($handle === false) {
        throw new RuntimeException("Could not create a unique filename.");
    }

    fclose($handle);

    if (!move_uploaded_file($tmp, $destination)) {
        unlink($destination);
        throw new RuntimeException("Could not save uploaded file.");
    }

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO student_files
            (student_id, category, original_name, stored_name,
             file_path, file_type, file_size)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $studentId,
            $category,
            basename($file['name']),
            $storedName,
            'uploads/' . $storedName,
            $mime,
            filesize($destination)
        ]);
    } catch (Throwable $e) {
        if (is_file($destination)) {
            unlink($destination);
        }

        throw $e;
    }
}