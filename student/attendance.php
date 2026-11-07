<?php
include "../config/auth.php";
require_role("student");
include "../config/database.php";

$student_id = $_SESSION['user']['id'];
$course_id  = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// جلب الكورسات للفلتر
$stmt = $conn->prepare("
    SELECT c.id, c.name FROM course_student cs
    JOIN courses c ON c.id = cs.course_id
    WHERE cs.student_id = ? ORDER BY c.name
");
$stmt->execute([$student_id]);
$courses = $stmt->fetchAll();

// جلب الحضور
$query = "
    SELECT c.name AS course_name, a.attendance_date, a.status
    FROM attendance a
    JOIN courses c ON c.id = a.course_id
    WHERE a.student_id = ?
";
$params = [$student_id];
if ($course_id > 0) {
    $query .= " AND a.course_id = ?";
    $params[] = $course_id;
}
$query .= " ORDER BY a.attendance_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$records = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance</title>
    <link rel="stylesheet" href="../assets/css/student.css">
</head>

<body>
    <div class="container">
        <a href="index.php" class="back-link">⬅ Back to Dashboard</a>

        <div class="header fade-up">
            <div>
                <h1>📅 All Attendance</h1>
                <p>Full history of your records</p>
            </div>
        </div>

        <div class="section fade-up delay-1">
            <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap;">
                <select name="course_id"
                    style="padding:10px; border-radius:12px; border:none; background:white; color:#333;">
                    <option value="">All Courses</option>
                    <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($course_id == $c['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>

        <div class="section fade-up delay-2">
            <?php if (empty($records)): ?>
            <p style="color:var(--text-muted); text-align:center;">No records.</p>
            <?php else: ?>
            <table style="width:100%; border-collapse:collapse; text-align:left;">
                <thead>
                    <tr style="border-bottom:1px solid var(--glass-border);">
                        <th style="padding:12px; color:var(--text-muted);">Course</th>
                        <th style="padding:12px; color:var(--text-muted);">Date</th>
                        <th style="padding:12px; color:var(--text-muted);">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $r): ?>
                    <tr>
                        <td style="padding:15px; font-weight:600;"><?= htmlspecialchars($r['course_name']) ?></td>
                        <td style="padding:15px;"><?= $r['attendance_date'] ?></td>
                        <td style="padding:15px;">
                            <?= $r['status'] === 'present' ? '✅ Present' : '❌ Absent' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <!-- THEME -->
        <div style="text-align:center; margin-top: 30px;" class="fade-up">
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
        const saved = localStorage.getItem("student_theme");
        if (saved) document.body.className = saved;
    });

    function setTheme(t) {
        document.body.className = t;
        localStorage.setItem("student_theme", t);
    }
    </script>
</body>

</html>