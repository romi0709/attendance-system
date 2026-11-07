<?php
include "../config/auth.php";
require_role("admin");
include "../config/database.php";

$total_students   = $conn->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$total_professors = $conn->query("SELECT COUNT(*) FROM users WHERE role='professor'")->fetchColumn();
$total_courses    = $conn->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$total_enrollments = $conn->query("SELECT COUNT(*) FROM course_student")->fetchColumn();

$attendance = $conn->query("SELECT status, COUNT(*) AS cnt FROM attendance GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
$present = 0; $absent = 0;
foreach ($attendance as $a) {
    if ($a['status'] === 'present') $present = (int)$a['cnt'];
    if ($a['status'] === 'absent')  $absent  = (int)$a['cnt'];
}
$total_attendance = $present + $absent;
$presentPct = $total_attendance > 0 ? round(($present / $total_attendance) * 100, 1) : 0;

$courseAttendance = $conn->query("
    SELECT c.id, c.name, SUM(a.status = 'present') AS presents, SUM(a.status = 'absent') AS absents
    FROM courses c
    LEFT JOIN attendance a ON a.course_id = c.id
    GROUP BY c.id
    ORDER BY c.name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <div class="container">
        <a href="index.php" class="back-link">⬅ Back to Dashboard</a>
        <div class="header fade-up">
            <div>
                <h1>📊 Statistics</h1>
                <p>Overview of the university system</p>
            </div>
            <div class="header-actions"><button onclick="window.print()" class="btn">🖨️ Print</button></div>
        </div>

        <div class="stats-grid fade-up delay-1">
            <div class="stat-card">
                <div class="stat-icon">🎓</div>
                <div class="stat-value text-success"><?= $total_students ?></div>
                <div class="stat-label">Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👨‍🏫</div>
                <div class="stat-value text-warning"><?= $total_professors ?></div>
                <div class="stat-label">Professors</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-value text-info"><?= $total_courses ?></div>
                <div class="stat-label">Courses</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📌</div>
                <div class="stat-value" style="color:#c4b5fd"><?= $total_enrollments ?></div>
                <div class="stat-label">Enrollments</div>
            </div>
        </div>

        <div class="section fade-up delay-2">
            <div class="section-title">📈 Attendance Overview</div>
            <div style="display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
                <div style="flex:1; min-width:200px;">
                    <p><strong><?= $present ?></strong> Present</p>
                    <p><strong><?= $absent ?></strong> Absent</p>
                    <p><strong><?= $presentPct ?>%</strong> Present Rate (out of <?= $total_attendance ?>)</p>
                </div>
                <div
                    style="width:150px; height:150px; border-radius:50%; background: conic-gradient(#10b981 0 <?= $presentPct ?>%, #ef4444 <?= $presentPct ?>% 100%);">
                </div>
            </div>
        </div>

        <div class="section fade-up delay-3">
            <div class="section-title">📚 Attendance by Course</div>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--glass-border);">
                            <th style="padding:12px; color:var(--text-muted);">Course</th>
                            <th style="padding:12px; color:var(--text-muted);">Presents</th>
                            <th style="padding:12px; color:var(--text-muted);">Absents</th>
                            <th style="padding:12px; color:var(--text-muted);">Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courseAttendance as $ca): 
                            $p = (int)$ca['presents']; $a = (int)$ca['absents']; $t = $p + $a; 
                            $rate = $t > 0 ? round(($p/$t)*100,1) : 0;
                        ?>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                            <td style="padding:15px; font-weight:600;"><?= htmlspecialchars($ca['name']) ?></td>
                            <td style="padding:15px; color:#6ee7b7;"><?= $p ?></td>
                            <td style="padding:15px; color:#fca5a5;"><?= $a ?></td>
                            <td style="padding:15px;"><?= $rate ?>%</td>
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