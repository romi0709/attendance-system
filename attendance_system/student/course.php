<?php
include "../config/auth.php";
require_role("student");
include "../config/database.php";

$student_id = $_SESSION['user']['id'];
$course_id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($course_id === 0) {
    die("No course selected. <a href='index.php'>Go back</a>");
}

// جلب معلومات الكورس
$stmt = $conn->prepare("
    SELECT c.name, u.fullname AS professor
    FROM course_student cs
    JOIN courses c ON c.id = cs.course_id
    LEFT JOIN users u ON u.id = c.professor_id
    WHERE cs.course_id = ? AND cs.student_id = ?
");
$stmt->execute([$course_id, $student_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    die("Course not found or you are not enrolled. <a href='index.php'>Go back</a>");
}

// إحصائيات هاد الكورس (ماشي كاملين)
$stmt = $conn->prepare("
    SELECT SUM(status = 'present') AS p, SUM(status = 'absent') AS a
    FROM attendance WHERE student_id = ? AND course_id = ?
");
$stmt->execute([$student_id, $course_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
$present = (int)($data['p'] ?? 0);
$absent  = (int)($data['a'] ?? 0);
$total   = $present + $absent;
$rate    = $total > 0 ? round(($present / $total) * 100) : 0;

// سجل الحضور ديال هاد الكورس
$stmt = $conn->prepare("
    SELECT attendance_date, status 
    FROM attendance 
    WHERE student_id = ? AND course_id = ?
    ORDER BY attendance_date DESC
");
$stmt->execute([$student_id, $course_id]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Details</title>
    <link rel="stylesheet" href="../assets/css/student.css">
</head>

<body>
    <div class="container">
        <a href="index.php" class="back-link">⬅ Back to Dashboard</a>

        <div class="header fade-up">
            <div>
                <h1>📚 <?= htmlspecialchars($course['name']) ?></h1>
                <p>👨‍🏫 Professor: <?= $course['professor'] ? htmlspecialchars($course['professor']) : 'N/A' ?></p>
            </div>
        </div>

        <!-- إحصائيات هاد الكورس بوحدو -->
        <div class="stats-grid fade-up delay-1">
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-value text-success"><?= $present ?></div>
                <div class="stat-label">Present</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">❌</div>
                <div class="stat-value text-danger"><?= $absent ?></div>
                <div class="stat-label">Absent</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-value text-warning"><?= $rate ?>%</div>
                <div class="stat-label">Course Rate</div>
            </div>
        </div>

        <!-- سجل الحضور ديال هاد الكورس -->
        <div class="section fade-up delay-2">
            <div class="section-title">📅 Attendance History (This Course Only)</div>
            <?php if (empty($records)): ?>
            <p style="color:var(--text-muted); text-align:center;">No attendance yet.</p>
            <?php else: ?>
            <table style="width:100%; border-collapse:collapse; text-align:left;">
                <thead>
                    <tr style="border-bottom:1px solid var(--glass-border);">
                        <th style="padding:12px; color:var(--text-muted);">Date</th>
                        <th style="padding:12px; color:var(--text-muted);">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $r): ?>
                    <tr>
                        <td style="padding:15px;"><?= htmlspecialchars($r['attendance_date']) ?></td>
                        <td style="padding:15px;">
                            <?php if ($r['status'] === 'present'): ?>
                            <span
                                style="background:rgba(16,185,129,0.2); color:#6ee7b7; padding:4px 10px; border-radius:20px;">Present</span>
                            <?php else: ?>
                            <span
                                style="background:rgba(239,68,68,0.2); color:#fca5a5; padding:4px 10px; border-radius:20px;">Absent</span>
                            <?php endif; ?>
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