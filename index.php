<?php
require 'db.php';
requireAdmin();

$stmt = $pdo->query(
    "SELECT * FROM students ORDER BY id DESC"
);
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Management</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f1f5f9;
    margin: 25px;
    color: #1e293b;
}
.container {
    max-width: 1100px;
    margin: auto;
    background: white;
    padding: 25px;
    border-radius: 10px;
}
h1 { color: #1d4ed8; }
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th, td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: left;
}
th { background: #2563eb; color: white; }
a, button {
    display: inline-block;
    padding: 8px 12px;
    border: 0;
    border-radius: 4px;
    color: white;
    background: #2563eb;
    text-decoration: none;
    cursor: pointer;
}
.delete { background: #dc2626; }
.add { margin-top: 10px; }
</style>
</head>
<body>
<div class="container">
<h1>Student Management System</h1>

<a class="add" href="add.php">+ Add Student</a>
<a href="logout.php">Logout</a>

<table>
<thead>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Roll Number</th>
    <th>Email</th>
    <th>Course</th>
    <th>Actions</th>
</tr>
</thead>
<tbody>
<?php if (!$students): ?>
<tr><td colspan="6">No students found.</td></tr>
<?php else: ?>
<?php foreach ($students as $s): ?>
<tr>
    <td><?= (int)$s['id'] ?></td>
    <td><?= h($s['name']) ?></td>
    <td><?= h($s['roll_number']) ?></td>
    <td><?= h($s['email']) ?></td>
    <td><?= h($s['course']) ?></td>
    <td>
        <a href="view.php?id=<?= (int)$s['id'] ?>">View</a>
        <a href="edit.php?id=<?= (int)$s['id'] ?>">Edit</a>

        <form action="delete.php" method="post"
              style="display:inline"
              onsubmit="return confirm('Delete this student?')">
            <input type="hidden" name="csrf_token"
                   value="<?= h(csrfToken()) ?>">
            <input type="hidden" name="id"
                   value="<?= (int)$s['id'] ?>">
            <button class="delete" type="submit">Delete</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</body>
</html>