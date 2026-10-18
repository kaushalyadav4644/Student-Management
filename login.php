<?php
require 'db.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        "SELECT id, username, password
         FROM admins WHERE username = ?"
    );
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        header('Location: index.php');
        exit;
    }

    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login</title>
</head>
<body>
<h1>Administrator Login</h1>

<?php if ($error): ?>
<p style="color:red"><?= h($error) ?></p>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="csrf_token"
           value="<?= h(csrfToken()) ?>">

    <p>Username:
    <input name="username" required></p>

    <p>Password:
    <input type="password" name="password" required></p>

    <button type="submit">Login</button>
</form>
</body>
</html>