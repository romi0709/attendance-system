<?php
include "../config/auth.php";
require_role("admin");
include "../config/database.php";

$success = $error = "";
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit_data = null;

if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'professor'");
    $stmt->execute([$edit_id]);
    $edit_data = $stmt->fetch();
}

// ➕ إضافة أستاذ
if (isset($_POST['add'])) {
    $name = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $raw_password = $_POST['password'];
    $password = password_hash($raw_password, PASSWORD_DEFAULT);
    $course_id = (int)$_POST['course_id'];

    $check = $conn->prepare("SELECT id FROM users WHERE email=?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $error = "This email is already used!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role, phone) VALUES (?, ?, ?, 'professor', ?)");
        $stmt->execute([$name, $email, $password, $phone]);
        $prof_id = $conn->lastInsertId();
        if ($course_id > 0) {
            $conn->prepare("UPDATE courses SET professor_id=? WHERE id=?")->execute([$prof_id, $course_id]);
        }
        $success = "✅ Professor added! Password: <b>" . htmlspecialchars($raw_password) . "</b>";
    }
}

// ✏️ تعديل أستاذ
if (isset($_POST['update_professor'])) {
    $id = (int)$_POST['id'];
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $new_password = $_POST['password'];

    $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check->execute([$email, $id]);

    if ($check->rowCount() > 0) {
        $error = "Email already used by another user!";
    } else {
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE users SET fullname=?, email=?, phone=?, password=? WHERE id=?");
            $upd->execute([$fullname, $email, $phone, $hashed, $id]);
            $success = "✅ Professor updated! New password: <b>" . htmlspecialchars($new_password) . "</b>";
        } else {
            $upd = $conn->prepare("UPDATE users SET fullname=?, email=?, phone=? WHERE id=?");
            $upd->execute([$fullname, $email, $phone, $id]);
            $success = "✅ Professor updated successfully!";
        }
        $edit_id = 0; $edit_data = null;
    }
}

// 🗑️ حذف أستاذ
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->prepare("UPDATE courses SET professor_id=NULL WHERE professor_id=?")->execute([$id]);
    $conn->prepare("DELETE FROM users WHERE id=? AND role='professor'")->execute([$id]);
    header("Location: professors.php"); exit;
}

// 🔍 جلب الأساتذة
if ($search !== "") {
    $stmt = $conn->prepare("SELECT * FROM users WHERE role='professor' AND (fullname LIKE ? OR email LIKE ?) ORDER BY fullname ASC");
    $stmt->execute(["%$search%", "%$search%"]);
    $professors = $stmt->fetchAll();
} else {
    $professors = $conn->query("SELECT * FROM users WHERE role='professor' ORDER BY id DESC")->fetchAll();
}
$courses = $conn->query("SELECT * FROM courses WHERE professor_id IS NULL")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Professors</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>
    <div class="container">
        <a href="index.php" class="back-link">⬅ Back to Dashboard</a>
        <div class="header fade-up">
            <div>
                <h1>👨‍🏫 Manage Professors</h1>
                <p>Add, edit, and manage professors</p>
            </div>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="section fade-up delay-1">
            <div class="section-title">
                <?= $edit_data ? '✏️ Edit Professor: ' . htmlspecialchars($edit_data['fullname']) : '➕ Add New Professor' ?>
            </div>
            <form method="POST" style="display:flex; gap:10px; flex-wrap:wrap;">
                <?php if ($edit_data): ?><input type="hidden" name="id" value="<?= $edit_data['id'] ?>"><?php endif; ?>

                <input type="text" name="fullname" placeholder="Fullname" required
                    value="<?= $edit_data ? htmlspecialchars($edit_data['fullname']) : '' ?>"
                    style="padding:12px; border-radius:10px; border:none; background:white; color:#333; flex:1; min-width:200px;">

                <input type="email" name="email" placeholder="Email" required
                    value="<?= $edit_data ? htmlspecialchars($edit_data['email']) : '' ?>"
                    style="padding:12px; border-radius:10px; border:none; background:white; color:#333; flex:1; min-width:200px;">

                <input type="text" name="password"
                    placeholder="<?= $edit_data ? 'New Password (leave empty to keep)' : 'Password' ?>"
                    <?= $edit_data ? '' : 'required' ?>
                    style="padding:12px; border-radius:10px; border:none; background:white; color:#333; flex:1; min-width:200px;">

                <input type="text" name="phone" placeholder="Phone"
                    value="<?= $edit_data ? htmlspecialchars($edit_data['phone'] ?? '') : '' ?>"
                    style="padding:12px; border-radius:10px; border:none; background:white; color:#333; flex:1; min-width:150px;">

                <?php if (!$edit_data): ?>
                <select name="course_id"
                    style="padding:12px; border-radius:10px; border:none; background:white; color:#333;">
                    <option value="">Assign Course (Optional)</option>
                    <?php foreach($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>

                <?php if ($edit_data): ?>
                <button type="submit" name="update_professor" class="btn btn-primary" style="background:#10b981;">💾
                    Save</button>
                <a href="professors.php" class="btn" style="text-decoration:none;">❌ Cancel</a>
                <?php else: ?>
                <button type="submit" name="add" class="btn btn-primary">Add Professor</button>
                <?php endif; ?>
            </form>
        </div>

        <div class="section fade-up delay-2">
            <div class="section-title" style="justify-content:space-between;">
                <span>📋 Professors List (<?= count($professors) ?>)</span>
                <form method="GET" style="display:flex; gap:5px; margin:0;">
                    <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>"
                        style="padding:8px 12px; border-radius:10px; border:none; background:rgba(255,255,255,0.2); color:white;">
                    <button type="submit" class="btn" style="padding:8px 12px;">🔍</button>
                </form>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--glass-border);">
                            <th style="padding:12px; color:var(--text-muted);">Fullname</th>
                            <th style="padding:12px; color:var(--text-muted);">Email</th>
                            <th style="padding:12px; color:var(--text-muted);">Phone</th>
                            <th style="padding:12px; color:var(--text-muted);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($professors as $p): ?>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                            <td style="padding:15px; font-weight:600;"><?= htmlspecialchars($p['fullname']) ?></td>
                            <td style="padding:15px;"><?= htmlspecialchars($p['email']) ?></td>
                            <td style="padding:15px;"><?= htmlspecialchars($p['phone'] ?? '-') ?></td>
                            <td style="padding:15px; display:flex; gap:5px;">
                                <a href="?edit=<?= $p['id'] ?>"
                                    style="background:rgba(59,130,246,0.2); color:#93c5fd; padding:6px 12px; border-radius:8px; text-decoration:none;">✏️
                                    Edit</a>
                                <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Delete this professor?')"
                                    style="background:rgba(239,68,68,0.2); color:#fca5a5; padding:6px 12px; border-radius:8px; text-decoration:none;">🗑️
                                    Delete</a>
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