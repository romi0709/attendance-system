<?php
include "../config/auth.php";
require_role("student");
include "../config/database.php";

$student_id = $_SESSION['user']['id'];
$name       = $_SESSION['user']['fullname'];

// Courses
$stmt = $conn->prepare("
    SELECT DISTINCT c.id, c.name, u.fullname AS professor
    FROM course_student cs
    JOIN courses c ON c.id = cs.course_id
    LEFT JOIN users u ON u.id = c.professor_id
    WHERE cs.student_id = ?
    ORDER BY c.name ASC
");
$stmt->execute([$student_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$stmt = $conn->prepare("
    SELECT SUM(status = 'present') AS p, SUM(status = 'absent') AS a
    FROM attendance WHERE student_id = ?
");
$stmt->execute([$student_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
$present = (int)($data['p'] ?? 0);
$absent  = (int)($data['a'] ?? 0);
$total   = $present + $absent;
$rate    = $total > 0 ? round(($present / $total) * 100) : 0;

// Per-course stats
$stmt = $conn->prepare("
    SELECT c.id, SUM(a.status = 'present') AS p, SUM(a.status = 'absent') AS ab
    FROM course_student cs
    JOIN courses c ON c.id = cs.course_id
    LEFT JOIN attendance a ON a.course_id = c.id AND a.student_id = cs.student_id
    WHERE cs.student_id = ? GROUP BY c.id
");
$stmt->execute([$student_id]);
$per_course = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $p = (int)$row['p']; $ab = (int)$row['ab']; $t = $p + $ab;
    $per_course[$row['id']] = [
        'present' => $p,
        'rate'    => $t > 0 ? round(($p / $t) * 100) : 0,
        'total'   => $t
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/student.css">
</head>

<body>
    <div class="container">

        <!-- HEADER -->
        <div class="header fade-up">
            <div>
                <h1>🎓 Welcome, <?= htmlspecialchars($name) ?></h1>
                <p>Here's your attendance overview</p>
            </div>
            <div class="header-actions">
                <a href="profile.php" class="btn">👤 Profile</a>
                <a href="../logout.php" class="btn btn-primary">Logout</a>
            </div>
        </div>

        <!-- STATS -->
        <div class="stats-grid fade-up delay-1">
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-value text-success"><?= $present ?></div>
                <div class="stat-label">Present Days</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">❌</div>
                <div class="stat-value text-danger"><?= $absent ?></div>
                <div class="stat-label">Absent Days</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-value text-warning"><?= $rate ?>%</div>
                <div class="stat-label">Global Rate</div>
            </div>
        </div>

        <!-- COURSES -->
        <div class="section fade-up delay-2">
            <div class="section-title">📚 Your Courses</div>
            <?php if (empty($courses)): ?>
            <p style="color:var(--text-muted)">You are not enrolled in any course.</p>
            <?php else: ?>
            <div class="course-list">
                <?php foreach ($courses as $c): 
                        $stat = $per_course[$c['id']] ?? ['present'=>0, 'rate'=>0, 'total'=>0];
                    ?>
                <div class="course-card">
                    <div class="course-name"><?= htmlspecialchars($c['name']) ?></div>
                    <div class="course-prof">👨‍🏫 <?= $c['professor'] ? htmlspecialchars($c['professor']) : 'N/A' ?>
                    </div>

                    <div class="progress-container">
                        <div class="progress-label">
                            <span><?= $stat['present'] ?> / <?= $stat['total'] ?> classes</span>
                            <strong><?= $stat['rate'] ?>%</strong>
                        </div>
                    </div>

                    <!-- رابط الكورس -->
                    <a href="course.php?id=<?= (int)$c['id'] ?>" class="btn"
                        style="display:block; text-align:center; margin-top:10px;">View Details</a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- THEME -->
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

    </div> <!-- END CONTAINER -->

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