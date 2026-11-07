<?php
include "../config/auth.php";
require_role("admin");
include "../config/database.php";

$message = "";

if (isset($_POST['add'])) {
    $name = trim($_POST['name']);
    $professor_id = (int)$_POST['professor_id'];

    $check = $conn->prepare("SELECT id FROM courses WHERE name = ?");
    $check->execute([$name]);

    if ($check->rowCount() > 0) {
        $message = "<div class='alert alert-danger'>Course already exists!</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO courses (name, professor_id) VALUES (?, ?)");
        $stmt->execute([$name, $professor_id]);
        $message = "<div class='alert alert-success'>Course added successfully!</div>";
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->prepare("DELETE FROM courses WHERE id=?")->execute([$id]);
    header("Location: courses.php"); exit;
}

$courses = $conn->query("
    SELECT courses.*, users.fullname AS professor 
    FROM courses 
    LEFT JOIN users ON users.id = courses.professor_id
    ORDER BY courses.id DESC
")->fetchAll();
$professors = $conn->query("SELECT id, fullname FROM users WHERE role='professor'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <div class="container">
        <a href="index.php" class="back-link">⬅ Back to Dashboard</a>
        <div class="header fade-up">
            <div>
                <h1>📚 Manage Courses</h1>
                <p>Add and assign courses</p>
            </div>
        </div>
        <?= $message ?>
        <div class="section fade-up delay-1">
            <div class="section-title">➕ Add Course</div>
            <form method="POST" style="display:flex; gap:10px; flex-wrap:wrap;">
                <input type="text" name="name" placeholder="Course Name" required
                    style="padding:12px; border-radius:10px; border:none; background:white; color:#333; flex:1; min-width:200px;">
                <select name="professor_id" required
                    style="padding:12px; border-radius:10px; border:none; background:white; color:#333;">
                    <option value="">Assign Professor</option>
                    <?php foreach ($professors as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['fullname']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="submit" name="add">Add Course</button>
            </form>
        </div>

        <div class="section fade-up delay-2">
            <div class="section-title">📋 Courses List</div>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--glass-border);">
                            <th style="padding:12px; color:var(--text-muted);">ID</th>
                            <th style="padding:12px; color:var(--text-muted);">Course</th>
                            <th style="padding:12px; color:var(--text-muted);">Professor</th>
                            <th style="padding:12px; color:var(--text-muted);">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $c): ?>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                            <td style="padding:15px;"><?= $c['id'] ?></td>
                            <td style="padding:15px; font-weight:600;"><?= htmlspecialchars($c['name']) ?></td>
                            <td style="padding:15px;">
                                <?= $c['professor'] ? htmlspecialchars($c['professor']) : '<i>No professor</i>' ?></td>
                            <td style="padding:15px;">
                                <a href="?delete=<?= $c['id'] ?>" onclick="return confirm('Delete this course?')"
                                    style="background:rgba(239,68,68,0.2); color:#fca5a5; padding:6px 12px; border-radius:8px; text-decoration:none;">Delete</a>
                            </td>
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