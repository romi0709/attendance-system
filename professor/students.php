<?php
include "../config/auth.php";
require_role("professor");
include "../config/database.php";

$prof_id = $_SESSION['user']['id'];
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$message = "";

// التأكد من أن الكورس ديال هاد الأستاذ
if ($course_id > 0) {
    $own = $conn->prepare("SELECT id FROM courses WHERE id=? AND professor_id=?");
    $own->execute([$course_id, $prof_id]);
    if ($own->rowCount() === 0) $course_id = 0;
}

// إضافة طالب للكورس
if (isset($_POST['enroll']) && $course_id > 0) {
    $student_id = (int)$_POST['student_id'];
    
    $check = $conn->prepare("SELECT * FROM course_student WHERE course_id=? AND student_id=?");
    $check->execute([$course_id, $student_id]);
    
    if ($check->rowCount() > 0) {
        $message = "<div class='alert alert-warning'>⚠️ Student already enrolled!</div>";
    } else {
        $conn->prepare("INSERT INTO course_student (course_id, student_id) VALUES (?, ?)")->execute([$course_id, $student_id]);
        $message = "<div class='alert alert-success'>✅ Student enrolled successfully!</div>";
    }
}

// حذف طالب من الكورس
if (isset($_GET['remove']) && $course_id > 0) {
    $student_id = (int)$_GET['remove'];
    $conn->prepare("DELETE FROM course_student WHERE course_id = ? AND student_id = ?")->execute([$course_id, $student_id]);
    header("Location: students.php?course_id=$course_id"); exit;
}

// جلب الكورسات ديال الأستاذ
$stmt = $conn->prepare("SELECT * FROM courses WHERE professor_id = ?");
$stmt->execute([$prof_id]);
$courses = $stmt->fetchAll();

// جلب الطلبة ديال الكورس المختار
$students = [];
if ($course_id > 0) {
    $q = $conn->prepare("
        SELECT DISTINCT u.id, u.fullname, u.email 
        FROM course_student cs
        JOIN users u ON u.id = cs.student_id
        WHERE cs.course_id = ?
        ORDER BY u.fullname ASC
    ");
    $q->execute([$course_id]);
    $students = $q->fetchAll();
}

// جلب كاع الطلبة (باش نقدرو نضيفوهم)
$all_students = $conn->query("SELECT id, fullname FROM users WHERE role='student' ORDER BY fullname ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="../assets/css/professor.css">
</head>

<body>
    <div class="container">
        <a href="index.php" class="back-link">⬅ Back to Dashboard</a>

        <div class="header fade-up">
            <div>
                <h1>🎓 Manage Students</h1>
                <p>Add or remove students from your courses</p>
            </div>
        </div>

        <?= $message ?>

        <!-- اختيار الكورس -->
        <div class="section fade-up delay-1">
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

        <?php if ($course_id > 0): ?>

        <!-- إضافة طالب -->
        <div class="section fade-up delay-2">
            <div class="section-title">➕ Add Student to Course</div>
            <form method="POST" style="display:flex; gap:10px; flex-wrap:wrap;">
                <select name="student_id" required
                    style="padding:12px; border-radius:10px; border:none; background:white; color:#333; flex:1;">
                    <option value="">-- Select Student --</option>
                    <?php foreach($all_students as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['fullname']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="enroll" class="btn btn-primary">➕ Add</button>
            </form>
        </div>

        <!-- قائمة الطلبة -->
        <div class="section fade-up delay-3">
            <div class="section-title">📋 Enrolled Students (<?= count($students) ?>)</div>
            <?php if(empty($students)): ?>
            <p style="color:var(--text-muted)">No students enrolled in this course yet.</p>
            <?php else: ?>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--glass-border);">
                            <th style="padding:12px; color:var(--text-muted);">ID</th>
                            <th style="padding:12px; color:var(--text-muted);">Fullname</th>
                            <th style="padding:12px; color:var(--text-muted);">Email</th>
                            <th style="padding:12px; color:var(--text-muted);">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($students as $s): ?>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                            <td style="padding:15px;"><?= $s['id'] ?></td>
                            <td style="padding:15px; font-weight:600;"><?= htmlspecialchars($s['fullname']) ?></td>
                            <td style="padding:15px;"><?= htmlspecialchars($s['email']) ?></td>
                            <td style="padding:15px;">
                                <a href="?course_id=<?= $course_id ?>&remove=<?= $s['id'] ?>"
                                    onclick="return confirm('Remove this student from the course?')" class="btn"
                                    style="background:rgba(239,68,68,0.2); color:#fca5a5; padding:6px 12px; font-size:0.85rem;">➖
                                    Remove</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
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