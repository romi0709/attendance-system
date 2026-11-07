<?php
include "../config/auth.php";
require_role("professor");
include "../config/database.php";

$prof_id = $_SESSION['user']['id'];

$stmt = $conn->prepare("SELECT * FROM courses WHERE professor_id = ?");
$stmt->execute([$prof_id]);
$courses = $stmt->fetchAll();

$filter_course = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$filter_date = isset($_GET['date']) ? $_GET['date'] : "";

$query = "
    SELECT a.*, u.fullname AS student_name, c.name AS course_name
    FROM attendance a
    JOIN users u ON a.student_id = u.id
    JOIN courses c ON a.course_id = c.id
    WHERE c.professor_id = ?
";
$params = [$prof_id];

if ($filter_course > 0) {
    $query .= " AND a.course_id = ? ";
    $params[] = $filter_course;
}
if ($filter_date != "") {
    $query .= " AND a.attendance_date = ? "; // صلحنا date لـ attendance_date
    $params[] = $filter_date;
}
$query .= " ORDER BY a.attendance_date DESC, student_name ASC ";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$records = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Summary</title>
    <link rel="stylesheet" href="../assets/css/professor.css">
</head>

<body>
    <div class="container">
        <a href="index.php" class="back-link">⬅ Back to Dashboard</a>

        <div class="header fade-up">
            <div>
                <h1>📊 Attendance Summary</h1>
                <p>View and filter attendance records</p>
            </div>
            <div class="header-actions"><button onclick="window.print()" class="btn">🖨️ Print</button></div>
        </div>

        <div class="section fade-up delay-1">
            <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap;">
                <select name="course_id"
                    style="padding:12px; border-radius:10px; border:none; background:white; color:#333; flex:1;">
                    <option value="">All Courses</option>
                    <?php foreach($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filter_course == $c['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="date" value="<?= htmlspecialchars($filter_date) ?>"
                    style="padding:12px; border-radius:10px; border:none; background:white; color:#333;">
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>

        <div class="section fade-up delay-2">
            <div class="section-title">Results (<?= count($records) ?>)</div>
            <?php if(empty($records)): ?>
            <p style="color:var(--text-muted)">No records found.</p>
            <?php else: ?>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--glass-border);">
                            <th style="padding:12px; color:var(--text-muted);">Date</th>
                            <th style="padding:12px; color:var(--text-muted);">Course</th>
                            <th style="padding:12px; color:var(--text-muted);">Student</th>
                            <th style="padding:12px; color:var(--text-muted);">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($records as $r): ?>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                            <td style="padding:15px;"><?= htmlspecialchars($r['attendance_date']) ?></td>
                            <td style="padding:15px; font-weight:600;"><?= htmlspecialchars($r['course_name']) ?></td>
                            <td style="padding:15px;"><?= htmlspecialchars($r['student_name']) ?></td>
                            <td style="padding:15px;">
                                <span
                                    style="background:<?= $r['status']=='present' ? 'rgba(16,185,129,0.2); color:#6ee7b7' : 'rgba(239,68,68,0.2); color:#fca5a5' ?>; padding:4px 10px; border-radius:20px; font-size:0.85rem; font-weight:600;">
                                    <?= ucfirst($r['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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