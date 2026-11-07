<?php
include "../config/auth.php";
require_role("admin");
include "../config/database.php";

$total_students = $conn->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$total_professors = $conn->query("SELECT COUNT(*) FROM users WHERE role='professor'")->fetchColumn();
$total_courses = $conn->query("SELECT COUNT(*) FROM courses")->fetchColumn();

$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT SUM(status='present') AS p, SUM(status='absent') AS a FROM attendance WHERE attendance_date = ?");
$stmt->execute([$today]);
$today_att = $stmt->fetch();
$present_today = (int)($today_att['p'] ?? 0);
$absent_today  = (int)($today_att['a'] ?? 0);

$latest_students = $conn->query("SELECT fullname, created_at FROM users WHERE role='student' ORDER BY id DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <div class="container">
        <div class="header fade-up">
            <div>
                <h1>🛡️ Admin Dashboard</h1>
                <p>Manage the university system</p>
            </div>
            <div class="header-actions">
                <a href="../logout.php" class="btn btn-primary">Logout</a>
            </div>
        </div>

        <div class="stats-grid fade-up delay-1">
            <div class="stat-card">
                <div class="stat-icon">🎓</div>
                <div class="stat-value text-success"><?= $total_students ?></div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👨‍🏫</div>
                <div class="stat-value text-warning"><?= $total_professors ?></div>
                <div class="stat-label">Total Professors</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-value text-info"><?= $total_courses ?></div>
                <div class="stat-label">Total Courses</div>
            </div>
        </div>

        <div class="section fade-up delay-2">
            <div class="section-title">📊 Today's Attendance (<?= date('d M Y') ?>)</div>
            <div class="stats-grid" style="margin-bottom:0;">
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value text-success"><?= $present_today ?></div>
                    <div class="stat-label">Present</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">❌</div>
                    <div class="stat-value text-danger"><?= $absent_today ?></div>
                    <div class="stat-label">Absent</div>
                </div>
            </div>
        </div>

        <div class="section fade-up delay-3">
            <div class="section-title">⚡ Quick Actions</div>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
                <a href="students.php" class="btn" style="padding:15px; text-align:center;">🎓 Manage Students</a>
                <a href="professors.php" class="btn" style="padding:15px; text-align:center;">👨‍🏫 Manage
                    Professors</a>
                <a href="courses.php" class="btn" style="padding:15px; text-align:center;">📚 Manage Courses</a>
                <a href="enroll.php" class="btn" style="padding:15px; text-align:center;">📌 Enroll Students</a>
                <a href="statistics.php" class="btn" style="padding:15px; text-align:center;">📊 View Statistics</a>
            </div>
        </div>

        <div class="section fade-up delay-3">
            <div class="section-title">🆕 Latest Registered Students</div>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--glass-border);">
                            <th style="padding:12px; color:var(--text-muted);">Fullname</th>
                            <th style="padding:12px; color:var(--text-muted);">Date Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($latest_students as $s): ?>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                            <td style="padding:15px; font-weight:600;"><?= htmlspecialchars($s['fullname']) ?></td>
                            <td style="padding:15px;">
                                <?= $s['created_at'] ? date('d M Y', strtotime($s['created_at'])) : 'N/A' ?></td>
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