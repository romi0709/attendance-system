<?php
include "../config/auth.php";
require_role("professor");
include "../config/database.php";

$prof_id = $_SESSION['user']['id'];
$name = $_SESSION['user']['fullname'];

// Stats
$stmt = $conn->prepare("SELECT COUNT(*) FROM courses WHERE professor_id = ?");
$stmt->execute([$prof_id]);
$total_courses = $stmt->fetchColumn();

$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT cs.student_id) 
    FROM course_student cs
    JOIN courses c ON cs.course_id = c.id
    WHERE c.professor_id = ?
");
$stmt->execute([$prof_id]);
$total_students = $stmt->fetchColumn();

// Recent courses
$stmt = $conn->prepare("SELECT * FROM courses WHERE professor_id = ? ORDER BY id DESC");
$stmt->execute([$prof_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Dashboard</title>
    <link rel="stylesheet" href="../assets/css/professor.css">
</head>

<body>
    <div class="container">
        <div class="header fade-up">
            <div>
                <h1>👨‍🏫 Welcome, <?= htmlspecialchars($name) ?></h1>
                <p>Manage your courses and attendance</p>
            </div>
            <div class="header-actions">
                <a href="profile.php" class="btn">👤 Profile</a>
                <a href="../logout.php" class="btn btn-primary">Logout</a>
            </div>
        </div>

        <div class="stats-grid fade-up delay-1">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-value text-success"><?= $total_courses ?></div>
                <div class="stat-label">My Courses</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🎓</div>
                <div class="stat-value text-warning"><?= $total_students ?></div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>

        <div class="section fade-up delay-2">
            <div class="section-title">⚡ Quick Actions</div>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
                <a href="attendance.php" class="btn" style="padding:15px; text-align:center;">📅 Take Attendance</a>
                <a href="courses.php" class="btn" style="padding:15px; text-align:center;">📚 My Courses</a>
                <a href="summary.php" class="btn" style="padding:15px; text-align:center;">📊 Summary</a>
                <a href="students.php" class="btn" style="padding:15px; text-align:center;">🎓 My Students</a>
            </div>
        </div>

        <div class="section fade-up delay-3">
            <div class="section-title">📋 Your Courses</div>
            <?php if (empty($courses)): ?>
            <p style="color:var(--text-muted)">No courses assigned. Ask admin to assign you courses.</p>
            <?php else: ?>
            <div class="course-list">
                <?php foreach($courses as $c): ?>
                <div class="course-card">
                    <div class="course-name"><?= htmlspecialchars($c['name']) ?></div>
                    <a href="attendance.php?course_id=<?= $c['id'] ?>" class="btn"
                        style="display:block; text-align:center; margin-top:10px;">Open Attendance</a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
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
        const saved = localStorage.getItem("prof_theme");
        if (saved) document.body.className = saved;
    });

    function setTheme(t) {
        document.body.className = t;
        localStorage.setItem("prof_theme", t);
    }
    </script>
</body>

</html>