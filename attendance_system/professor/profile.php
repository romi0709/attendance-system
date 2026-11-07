<?php
include "../config/auth.php";
require_role("professor");
include "../config/database.php";

$prof_id = $_SESSION['user']['id'];
$success = $error = "";

if (isset($_POST['save'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    $ch = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $ch->execute([$email, $prof_id]);
    if ($ch->rowCount() > 0) {
        $error = "Email already in use.";
    } else {
        $upd = $conn->prepare("UPDATE users SET fullname = ?, email = ?, phone = ? WHERE id = ?");
        $upd->execute([$fullname, $email, $phone, $prof_id]);
        $_SESSION['user']['fullname'] = $fullname;
        $_SESSION['user']['email'] = $email;
        $success = "Profile updated successfully.";
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$prof_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$initial = strtoupper(substr($user['fullname'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Profile</title>
    <link rel="stylesheet" href="../assets/css/professor.css">
    <style>
    .profile-header {
        display: flex;
        align-items: center;
        gap: 25px;
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        padding: 30px;
        border-radius: 24px;
        margin-bottom: 2rem;
        box-shadow: var(--glass-shadow);
        flex-wrap: wrap;
    }

    .avatar-big {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ffffff, #e2e8f0);
        color: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        font-weight: 800;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        flex-shrink: 0;
    }

    .profile-info h2 {
        font-size: 2rem;
        margin-bottom: 5px;
    }

    .profile-info p {
        color: var(--text-muted);
        font-size: 1.1rem;
        margin-bottom: 15px;
    }
    </style>
</head>

<body>
    <div class="container">
        <a href="index.php" class="back-link">⬅ Back to Dashboard</a>

        <div class="profile-header fade-up">
            <div class="avatar-big"><?= $initial ?></div>
            <div class="profile-info">
                <h2><?= htmlspecialchars($user['fullname']) ?></h2>
                <p>📧 <?= htmlspecialchars($user['email']) ?></p>
                <span
                    style="display:inline-block; background:rgba(255,255,255,0.15); padding:6px 16px; border-radius:20px; font-size:0.85rem; font-weight:600; border:1px solid rgba(255,255,255,0.2);">👨‍🏫
                    Professor</span>
            </div>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="section fade-up delay-1">
            <div class="section-title">✏️ Update Profile</div>
            <form method="POST" style="display:flex; flex-direction:column; gap:15px; max-width:500px;">
                <div>
                    <label style="color:var(--text-muted); font-size:0.9rem;">Fullname</label>
                    <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required
                        style="width:100%; padding:12px; border-radius:10px; border:none; background:white; color:#333; margin-top:5px;">
                </div>
                <div>
                    <label style="color:var(--text-muted); font-size:0.9rem;">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required
                        style="width:100%; padding:12px; border-radius:10px; border:none; background:white; color:#333; margin-top:5px;">
                </div>
                <div>
                    <label style="color:var(--text-muted); font-size:0.9rem;">Phone</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                        style="width:100%; padding:12px; border-radius:10px; border:none; background:white; color:#333; margin-top:5px;">
                </div>
                <button type="submit" name="save" class="btn btn-primary" style="margin-top:10px;">💾 Save
                    Changes</button>
            </form>
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