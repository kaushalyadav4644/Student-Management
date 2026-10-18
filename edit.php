<?php
require 'db.php';
require 'upload.php';
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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name = trim($_POST['name'] ?? '');
    $roll = trim($_POST['roll_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');

    if ($name === '' || strlen($name) > 100) {
        $error = 'Enter a valid name.';
    } elseif ($roll === '' || strlen($roll) > 30) {
        $error = 'Enter a valid roll number.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif ($course === '' || strlen($course) > 100) {
        $error = 'Enter a valid course.';
    }

    if (!$error) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "UPDATE students
                 SET name=?, roll_number=?, email=?, course=?
                 WHERE id=?"
            );
            $stmt->execute([$name, $roll, $email, $course, $id]);

            if (isset($_FILES['profile'])) {
                saveStudentFile(
                    $pdo, $id, $_FILES['profile'], 'profile'
                );
            }

            if (isset($_FILES['documents'])) {
                $docs = $_FILES['documents'];

                foreach ($docs['name'] as $i => $filename) {
                    $file = [
                        'name' => $docs['name'][$i],
                        'type' => $docs['type'][$i],
                        'tmp_name' => $docs['tmp_name'][$i],
                        'error' => $docs['error'][$i],
                        'size' => $docs['size'][$i]
                    ];

                    saveStudentFile(
                        $pdo, $id, $file, 'document'
                    );
                }
            }

            $pdo->commit();

            header("Location: view.php?id=" . $id);
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log($e->getMessage());
            $error = $e instanceof PDOException
                ? 'Unable to update. The roll number may already exist.'
                : $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Student</title>
</head>
<body>
<h1>Edit Student</h1>

<?php if ($error): ?>
<p style="color:red"><?= h($error) ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token"
           value="<?= h(csrfToken()) ?>">

    <p>Name:
    <input name="name" maxlength="100" required
           value="<?= h($student['name']) ?>"></p>

    <p>Roll Number:
    <input name="roll_number" maxlength="30" required
           value="<?= h($student['roll_number']) ?>"></p>

    <p>Email:
    <input type="email" name="email" maxlength="150" required
           value="<?= h($student['email']) ?>"></p>

    <p>Course:
    <input name="course" maxlength="100" required
           value="<?= h($student['course']) ?>"></p>

    <p>Add profile image:
    <input type="file" name="profile"
           accept=".jpg,.jpeg,.png,image/jpeg,image/png"></p>

    <p>Add documents:
    <input type="file" name="documents[]" multiple
           accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"></p>

    <button type="submit">Update Student</button>
    <a href="view.php?id=<?= (int)$id ?>">Cancel</a>
</form>
</body>
</html>