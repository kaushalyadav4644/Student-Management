<?php
require 'db.php';
requireAdmin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit('Invalid student ID.');
}

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    exit('Student not found.');
}

$stmt = $pdo->prepare(
    "SELECT * FROM student_files
     WHERE student_id = ?
     ORDER BY uploaded_at DESC"
);
$stmt->execute([$id]);
$files = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Student</title>
<style>
body { font-family: Arial, sans-serif; margin: 25px; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
th { background: #2563eb; color: white; }
img { max-width: 150px; max-height: 150px; }
</style>
</head>
<body>
<h1>Student Details</h1>

<p><strong>Name:</strong> <?= h($student['name']) ?></p>
<p><strong>Roll Number:</strong> <?= h($student['roll_number']) ?></p>
<p><strong>Email:</strong> <?= h($student['email']) ?></p>
<p><strong>Course:</strong> <?= h($student['course']) ?></p>
<p><strong>Created:</strong> <?= h($student['created_at']) ?></p>

<a href="index.php">Back to Students</a>
<a href="edit.php?id=<?= (int)$id ?>">Edit Student</a>

<h2>Uploaded Files</h2>

<table>
<tr>
    <th>Category</th>
    <th>File</th>
    <th>Type</th>
    <th>Size</th>
    <th>Upload Date</th>
    <th>Action</th>
</tr>

<?php if (!$files): ?>
<tr><td colspan="6">No files uploaded.</td></tr>
<?php endif; ?>

<?php foreach ($files as $file): ?>
<tr>
    <td><?= h($file['category']) ?></td>
    <td>
        <?php if (
            $file['category'] === 'profile' &&
            str_starts_with($file['file_type'], 'image/')
        ): ?>
            <img src="file.php?id=<?= (int)$file['id'] ?>&mode=view"
                 alt="Student profile image"><br>
        <?php endif; ?>

        <?= h($file['original_name']) ?>
    </td>
    <td><?= h($file['file_type']) ?></td>
    <td><?= number_format($file['file_size'] / 1024, 2) ?> KB</td>
    <td><?= h($file['uploaded_at']) ?></td>
    <td>
        <a href="file.php?id=<?= (int)$file['id'] ?>&mode=download">
            Download
        </a>
        |
        <a href="file.php?id=<?= (int)$file['id'] ?>&mode=view"
           target="_blank" rel="noopener">
            Open
        </a>
    </td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>