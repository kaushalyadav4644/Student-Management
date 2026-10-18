<?php
require 'db.php';
require 'upload.php';
requireAdmin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name = trim($_POST['name'] ?? '');
    $roll = trim($_POST['roll_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');

    if ($name === '' || strlen($name) > 100) {
        $error = 'Enter a valid student name.';
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
                "INSERT INTO students
                (name, roll_number, email, course)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->execute([$name, $roll, $email, $course]);
            $studentId = (int)$pdo->lastInsertId();

            if (isset($_FILES['profile'])) {
                saveStudentFile(
                    $pdo, $studentId, $_FILES['profile'], 'profile'
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
                        $pdo, $studentId, $file, 'document'
                    );
                }
            }

            $pdo->commit();

            header("Location: view.php?id=" . $studentId);
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log($e->getMessage());

            $error = $e instanceof PDOException
                ? 'Unable to save student. Check that the roll number is unique.'
                : $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Student</title>
</head>
<body>
<h1>Add Student</h1>

<?php if ($error): ?>
<p style="color:red"><?= h($error) ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token"
           value="<?= h(csrfToken()) ?>">

    <p>Name:
    <input type="text" name="name" maxlength="100" required></p>

    <p>Roll Number:
    <input type="text" name="roll_number" maxlength="30" required></p>

    <p>Email:
    <input type="email" name="email" maxlength="150" required></p>

    <p>Course:
    <input type="text" name="course" maxlength="100" required></p>

    <p>Profile Image (JPEG/PNG, maximum 2 MB):
    <input type="file" name="profile"
           accept=".jpg,.jpeg,.png,image/jpeg,image/png"></p>

    <p>Documents (PDF/JPEG/PNG, maximum 2 MB each):
    <input type="file" name="documents[]" multiple
           accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"></p>

    <button type="submit">Save Student</button>
    <a href="index.php">Back</a>
</form>
</body>
</html>