<?php
include "../config/auth.php";
require_role("admin");
include "../config/database.php";

$message = "";

if (isset($_POST['enroll'])) {
    $course_id = (int)$_POST['course_id'];
    $student_id = (int)$_POST['student_id'];

    $check = $conn->prepare("SELECT * FROM course_student WHERE course_id = ? AND student_id = ?");
    $check->execute([$course_id, $student_id]);

    if ($check->rowCount() > 0) {
        $message = "<div class='alert alert-warning'>Student already enrolled!</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO course_student (course_id, student_id) VALUES (?, ?)");
        $stmt->execute([$course_id, $student_id]);
        $message = "<div class='alert alert-success'>Student enrolled successfully!</div>";
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->prepare("DELETE FROM course_student WHERE id=?")->execute([$id]);
    header("Location: enroll.php"); exit;
}

$courses = $conn->query("SELECT * FROM courses ORDER BY name ASC")->fetchAll();
$students = $conn->query("SELECT * FROM users WHERE role = 'student' ORDER BY fullname ASC")->fetchAll();
$enrollments = $conn->query("
    SELECT cs.id, c.name AS course, u.fullname AS student
    FROM course_student cs
    JOIN courses c ON cs.course_id = c.id
    JOIN users u ON cs.student_id = u.id
    ORDER BY cs.id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll Students</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <div class="container">
        <a href="index.php" class="back-link">⬅ Back to Dashboard</a>
        <div class="header fade-up">
            <div>
                <h1>📌 Enroll Students</h1>
                <p>Assign students to courses</p>
            </div>
        </div>
        <?= $message ?>
        <div class="section fade-up delay-1">
            <div class="section-title">➕ Enroll Student</div>
            <form method="POST" style="display:flex; gap:10px; flex-wrap:wrap;">
                <select name="course_id" required
                    style="padding:12px; border-radius:10px; border:none; background:white; color:#333; flex:1;">
                    <option value="">Select Course</option>
                    <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="student_id" required
                    style="padding:12px; border-radius:10px; border:none; background:white; color:#333; flex:1;">
                    <option value="">Select Student</option>
                    <?php foreach ($students as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['fullname']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="submit" name="enroll">Enroll</button>
            </form>
        </div>

        <div class="section fade-up delay-2">
            <div class="section-title">📋 Current Enrollments</div>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--glass-border);">
                            <th style="padding:12px; color:var(--text-muted);">ID</th>
                            <th style="padding:12px; color:var(--text-muted);">Course</th>
                            <th style="padding:12px; color:var(--text-muted);">Student</th>
                            <th style="padding:12px; color:var(--text-muted);">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $e): ?>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                            <td style="padding:15px;"><?= $e['id'] ?></td>
                            <td style="padding:15px; font-weight:600;"><?= htmlspecialchars($e['course']) ?></td>
                            <td style="padding:15px;"><?= htmlspecialchars($e['student']) ?></td>
                            <td style="padding:15px;">
                                <a href="?delete=<?= $e['id'] ?>" onclick="return confirm('Remove this enrollment?')"
                                    style="background:rgba(239,68,68,0.2); color:#fca5a5; padding:6px 12px; border-radius:8px; text-decoration:none;">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="text-align:center; margin-top: 30px;" class="fade-up delay-3">
            <p style="color:var(--text-muted); font-size:0.8rem; margin-bottom:10px;">🎨 Choose Theme</p>
            <div class="themes">
                <button class="theme-btn" style="background:#4f46e5" onclick="setTheme('')"></button>
                <button class="theme-btn" style="background:#be185d" onclick="setTheme('theme-rose')"></button>
                <button class="theme-btn" style="background:#047857" onclick="setTheme('theme-vert')"></button>
                <button class="theme-btn" style="background:#0f766e" onclick="setTheme('theme-turquoise')"></button>
                <button class="theme-btn" style="background:#1f2937" onclick="setTheme('theme-noir')"></button>
                <button class="theme-btn" style="background:#b45309" onclick="setTheme('theme-gold')"></button>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const saved = localStorage.getItem("admin_theme");
        if (saved) document.body.className = saved;
    });

    function setTheme(t) {
        document.body.className = t;
        localStorage.setItem("admin_theme", t);
    }
    </script>
</body>

</html>