<?php
include "../config/auth.php";
require_role("professor");
include "../config/database.php";

$professor_id = $_SESSION["user_id"];
$message = "";

/* Fetch only courses belonging to this professor */
$courses = $conn->prepare("SELECT * FROM courses WHERE professor_id = ? ORDER BY name ASC");
$courses->execute([$professor_id]);
$courses = $courses->fetchAll();

/* Fetch students (all students) */
$students = $conn->query("SELECT * FROM users WHERE role='student' ORDER BY fullname ASC")->fetchAll();

/* Enroll student in professor's course */
if (isset($_POST['enroll'])) {
    $course_id = $_POST['course_id'];
    $student_id = $_POST['student_id'];

    // Validate course belongs to professor
    $checkCourse = $conn->prepare("SELECT id FROM courses WHERE id = ? AND professor_id = ?");
    $checkCourse->execute([$course_id, $professor_id]);

    if ($checkCourse->rowCount() === 0) {
        $message = "<p style='color:red;font-weight:bold;'>❌ You cannot enroll students in a course you do not teach.</p>";
    } else {
        // Check if already enrolled
        $check = $conn->prepare("SELECT * FROM course_student WHERE course_id=? AND student_id=?");
        $check->execute([$course_id, $student_id]);

        if ($check->rowCount() > 0) {
            $message = "<p style='color:red;font-weight:bold;'>⚠️ Student is already enrolled in this course!</p>";
        } else {
            $stmt = $conn->prepare("INSERT INTO course_student (course_id, student_id) VALUES (?, ?)");
            $stmt->execute([$course_id, $student_id]);
            $message = "<p style='color:lightgreen;font-weight:bold;'>✔ Student enrolled successfully!</p>";
        }
    }
}

/* Delete enrollment (professor can delete only from his courses) */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $checkDel = $conn->prepare("
        SELECT cs.id 
        FROM course_student cs
        JOIN courses c ON cs.course_id = c.id
        WHERE cs.id = ? AND c.professor_id = ?
    ");
    $checkDel->execute([$id, $professor_id]);

    if ($checkDel->rowCount() > 0) {
        $conn->prepare("DELETE FROM course_student WHERE id=?")->execute([$id]);
    }
}

/* Fetch enrollments for only professor's courses */
$enrollments = $conn->prepare("
    SELECT cs.id, c.name AS course, u.fullname AS student
    FROM course_student cs
    JOIN courses c ON cs.course_id = c.id
    JOIN users u ON cs.student_id = u.id
    WHERE c.professor_id = ?
    ORDER BY cs.id DESC
");
$enrollments->execute([$professor_id]);
$enrollments = $enrollments->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Professor — Enroll Students</title>
    <style>
    :root {
        --color1: #6a5af9;
        --color2: #a178ff;
    }

    body.theme-rose {
        --color1: #ff6fb7;
        --color2: #ffa0d6;
    }

    body.theme-vert {
        --color1: #4caf50;
        --color2: #81c784;
    }

    body.theme-turquoise {
        --color1: #00bfa6;
        --color2: #5ef7d7;
    }

    body.theme-noir {
        --color1: #222;
        --color2: #555;
    }

    body.theme-gold {
        --color1: #c59d00;
        --color2: #f7d64a;
    }

    body {
        margin: 0;
        font-family: Poppins;
        padding: 40px;
        color: #fff;
        background: linear-gradient(135deg, var(--color1), var(--color2));
        min-height: 100vh;
    }

    .container {
        width: 90%;
        margin: auto;
    }

    .card {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(12px);
        padding: 25px;
        border-radius: 18px;
        margin-bottom: 30px;
    }

    select {
        padding: 12px;
        width: 260px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.85);
        border: none;
    }

    button.add {
        padding: 12px 22px;
        background: var(--color1);
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-weight: bold;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    th,
    td {
        padding: 12px;
        background: rgba(255, 255, 255, 0.10);
    }

    th {
        background: rgba(255, 255, 255, 0.30);
    }

    td a {
        background: #ff3b3b;
        padding: 6px 12px;
        border-radius: 6px;
        color: white;
        text-decoration: none;
    }
    </style>

    <script>
    function setTheme(t) {
        document.body.className = "theme-" + t;
    }
    </script>
</head>

<body>

    <div class="container">

        <h1>📘 Enroll Students (Professor Panel)</h1>

        <div class="card">
            <h3>Assign Student to Your Course</h3>
            <?= $message ?>

            <form method="POST">
                <select name="course_id" required>
                    <option value="">-- Select Your Course --</option>
                    <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="student_id" required>
                    <option value="">-- Select Student --</option>
                    <?php foreach ($students as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= $s['fullname'] ?></option>
                    <?php endforeach; ?>
                </select>

                <button class="add" type="submit" name="enroll">Enroll</button>
            </form>
        </div>

        <div class="card">
            <h3>Current Enrollments (Your Courses)</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Course</th>
                    <th>Student</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($enrollments as $e): ?>
                <tr>
                    <td><?= $e['id'] ?></td>
                    <td><?= $e['course'] ?></td>
                    <td><?= $e['student'] ?></td>
                    <td><a href="?delete=<?= $e['id'] ?>">Delete</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

    </div>
</body>

</html>