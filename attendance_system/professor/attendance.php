<?php
include "../config/auth.php";
require_role("professor");
include "../config/database.php";

$prof_id = $_SESSION['user']['id'];
$message = "";
$course_id = null;
$students = [];

// جلب كورسات الأستاذ
$stmt = $conn->prepare("SELECT * FROM courses WHERE professor_id = ?");
$stmt->execute([$prof_id]);
$courses = $stmt->fetchAll();

// جلب الطلبة ديال كورس معين
if (isset($_GET['course_id'])) {
    $course_id = (int)$_GET['course_id'];
    
    // تأكد أن الكورس ديال هاد الأستاذ
    $own = $conn->prepare("SELECT id FROM courses WHERE id=? AND professor_id=?");
    $own->execute([$course_id, $prof_id]);
    
    if ($own->rowCount() > 0) {
        $q = $conn->prepare("
            SELECT DISTINCT u.id, u.fullname 
            FROM course_student cs
            JOIN users u ON u.id = cs.student_id
            WHERE cs.course_id = ?
            ORDER BY u.fullname ASC
        ");
        $q->execute([$course_id]);
        $students = $q->fetchAll();
    } else {
        $course_id = null;
    }
}

// حفظ الحضور
if (isset($_POST['save_attendance'])) {
    $course_id = (int)$_POST['course_id'];
    $date = $_POST['date'];

    $own = $conn->prepare("SELECT id FROM courses WHERE id=? AND professor_id=?");
    $own->execute([$course_id, $prof_id]);

    if ($own->rowCount() > 0) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if ($d && $d->format('Y-m-d') === $date) {
            $stmt = $conn->prepare("
                INSERT INTO attendance (course_id, student_id, attendance_date, status)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE status = VALUES(status)
            ");
            foreach ($_POST['status'] as $student_id => $status) {
                $student_id = (int)$student_id;
                $status = in_array($status, ['present', 'absent'], true) ? $status : 'absent';
                $stmt->execute([$course_id, $student_id, $date, $status]);
            }
            $message = "<div class='alert alert-success'>✅ Attendance saved successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Invalid date.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>❌ You can't record attendance for this course.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Attendance</title>
    <link rel="stylesheet" href="../assets/css/professor.css">
</head>

<body>
    <div class="container">
        <a href="index.php" class="back-link">⬅ Back to Dashboard</a>

        <div class="header fade-up">
            <div>
                <h1>📅 Take Attendance</h1>
                <p>Mark students present or absent</p>
            </div>
        </div>

        <?= $message ?>

        <div class="section fade-up delay-1">
            <div class="section-title">Select Course</div>
            <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap;">
                <select name="course_id" required
                    style="padding:12px; border-radius:10px; border:none; background:white; color:#333; flex:1;">
                    <option value="">-- Select Course --</option>
                    <?php foreach($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($course_id == $c['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Open</button>
            </form>
        </div>

        <?php if ($course_id && empty($students)): ?>
        <div class="alert alert-warning fade-up">⚠ No students enrolled in this course.</div>
        <?php endif; ?>

        <?php if (!empty($students)): ?>
        <div class="section fade-up delay-2">
            <div class="section-title">Mark Attendance — <?= date('d M Y') ?></div>
            <form method="POST">
                <input type="hidden" name="course_id" value="<?= $course_id ?>">
                <input type="date" name="date" value="<?= date('Y-m-d') ?>" required
                    style="padding:10px; border-radius:10px; border:none; margin-bottom:15px; background:white; color:#333;">

                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; text-align:left;">
                        <thead>
                            <tr style="border-bottom:1px solid var(--glass-border);">
                                <th style="padding:12px; color:var(--text-muted);">Student</th>
                                <th style="padding:12px; color:var(--text-muted);">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($students as $s): ?>
                            <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                                <td style="padding:15px; font-weight:600;"><?= htmlspecialchars($s['fullname']) ?></td>
                                <td style="padding:15px;">
                                    <select name="status[<?= $s['id'] ?>]"
                                        style="padding:8px 12px; border-radius:8px; border:none; background:white; color:#333;">
                                        <option value="present">Present</option>
                                        <option value="absent">Absent</option>
                                    </select>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" name="save_attendance" class="btn btn-primary"
                    style="margin-top:20px; width:100%; padding:15px; font-size:1.1rem;">💾 Save Attendance</button>
            </form>
        </div>
        <?php endif; ?>

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