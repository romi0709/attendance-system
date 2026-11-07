<?php
include "../config/auth.php";
require_role("professor");
include "../config/database.php";

$prof_id = $_SESSION['user']['id'];
$message = "";
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit_data = null;

// جلب بيانات الكورس إلا كنا فـ وضعية التعديل
if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ? AND professor_id = ?");
    $stmt->execute([$edit_id, $prof_id]);
    $edit_data = $stmt->fetch();
}

// تعديل الكورس
if (isset($_POST['update_course'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    
    $upd = $conn->prepare("UPDATE courses SET name = ? WHERE id = ? AND professor_id = ?");
    $upd->execute([$name, $id, $prof_id]);
    $message = "<div class='alert alert-success'>✅ Course updated successfully!</div>";
    $edit_data = null;
}

// حذف الكورس
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->prepare("DELETE FROM courses WHERE id = ? AND professor_id = ?")->execute([$id, $prof_id]);
    header("Location: courses.php"); exit;
}

// جلب كورسات الأستاذ
$stmt = $conn->prepare("SELECT * FROM courses WHERE professor_id = ? ORDER BY id DESC");
$stmt->execute([$prof_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses</title>
    <link rel="stylesheet" href="../assets/css/professor.css">
</head>

<body>
    <div class="container">
        <a href="index.php" class="back-link">⬅ Back to Dashboard</a>

        <div class="header fade-up">
            <div>
                <h1>📚 My Courses</h1>
                <p>Manage courses you teach</p>
            </div>
        </div>

        <?= $message ?>

        <!-- فورم التعديل (كيظهر غير ملي كتكليكي على Edit) -->
        <?php if ($edit_data): ?>
        <div class="section fade-up">
            <div class="section-title">✏️ Edit Course: <?= htmlspecialchars($edit_data['name']) ?></div>
            <form method="POST" style="display:flex; gap:10px; flex-wrap:wrap;">
                <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                <input type="text" name="name" value="<?= htmlspecialchars($edit_data['name']) ?>" required
                    style="padding:12px; border-radius:10px; border:none; background:white; color:#333; flex:1;">
                <button type="submit" name="update_course" class="btn btn-primary" style="background:#10b981;">💾 Save
                    Changes</button>
                <a href="courses.php" class="btn" style="text-decoration:none;">❌ Cancel</a>
            </form>
        </div>
        <?php endif; ?>

        <div class="section fade-up delay-1">
            <?php if(empty($courses)): ?>
            <p style="color:var(--text-muted)">No courses assigned yet.</p>
            <?php else: ?>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--glass-border);">
                            <th style="padding:12px; color:var(--text-muted);">ID</th>
                            <th style="padding:12px; color:var(--text-muted);">Course Name</th>
                            <th style="padding:12px; color:var(--text-muted);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($courses as $c): ?>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                            <td style="padding:15px;"><?= $c['id'] ?></td>
                            <td style="padding:15px; font-weight:600;"><?= htmlspecialchars($c['name']) ?></td>
                            <td style="padding:15px; display:flex; gap:5px; flex-wrap:wrap;">
                                <a href="attendance.php?course_id=<?= $c['id'] ?>" class="btn"
                                    style="padding:6px 12px; font-size:0.85rem;">📅 Attendance</a>
                                <a href="students.php?course_id=<?= $c['id'] ?>" class="btn"
                                    style="padding:6px 12px; font-size:0.85rem;">🎓 Students</a>
                                <a href="?edit=<?= $c['id'] ?>" class="btn"
                                    style="background:rgba(59,130,246,0.2); color:#93c5fd; padding:6px 12px; font-size:0.85rem;">✏️
                                    Edit</a>
                                <a href="?delete=<?= $c['id'] ?>" onclick="return confirm('Delete course?')" class="btn"
                                    style="background:rgba(239,68,68,0.2); color:#fca5a5; padding:6px 12px; font-size:0.85rem;">🗑️
                                    Delete</a>
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