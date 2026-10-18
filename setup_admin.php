<?php
require 'db.php';

$username = 'admin';
$password = 'Admin@123';

$stmt = $pdo->prepare(
    "SELECT id FROM admins WHERE username = ?"
);
$stmt->execute([$username]);

if ($stmt->fetch()) {
    exit('Admin already exists. Delete this file now.');
}

$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);

$stmt = $pdo->prepare(
    "INSERT INTO admins (username, password)
     VALUES (?, ?)"
);
$stmt->execute([$username, $hashedPassword]);

echo "Admin created successfully.<br>";
echo "Username: admin<br>";
echo "Temporary password: Admin@123<br>";
echo "Delete setup_admin.php immediately.";