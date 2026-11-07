<?php
include "../config/auth.php";
require_role("student");
include "../config/database.php";

$student_id = $_SESSION['user']['id'];

// جلب معلومات الطالب كاملة من قاعدة البيانات (الاسم، الإيميل، الهاتف، تاريخ التسجيل، المجموعة)
$stmt = $conn->prepare("SELECT fullname, email, phone, created_at, group_id FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$name    = $user['fullname'];
$email   = $user['email'];
$phone   = $user['phone'] ?? 'Not provided';
$initial = strtoupper(substr($name, 0, 1));

// تنسيق تاريخ التسجيل
$joined_date = $user['created_at'] ? date('d M Y', strtotime($user['created_at'])) : 'N/A';
$group_name  = $user['group_id'] ? 'Group ' . $user['group_id'] : 'N/A';

// عدد الكورسات
$stmt = $conn->prepare("SELECT COUNT(*) FROM course_student WHERE student_id = ?");
$stmt->execute([$student_id]);
$course_count = $stmt->fetchColumn();

// إحصائيات الحضور العامة
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="../assets/css/student.css">
    <style>
    .profile-header {
        display: flex;
        align-items: center;
        gap: 30px;
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        padding: 35px;
        border-radius: 24px;
        margin-bottom: 2rem;
        box-shadow: var(--glass-shadow);
        flex-wrap: wrap;
    }

    .avatar-big {
        width: 130px;
        height: 130px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ffffff, #e2e8f0);
        color: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4.5rem;
        font-weight: 800;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        flex-shrink: 0;
        border: 4px solid rgba(255, 255, 255, 0.3);
    }

    .profile-info {
        flex: 1;
        min-width: 250px;
    }

    .profile-info h2 {
        font-size: 2.2rem;
        margin-bottom: 5px;
    }

    .profile-info p {
        color: var(--text-muted);
        font-size: 1.1rem;
        margin-bottom: 15px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 15px;
        background: rgba(0, 0, 0, 0.1);
        padding: 20px;
        border-radius: 16px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 1rem;
        font-weight: 600;
        margin-top: 3px;
    }
    </style>
</head>

<body>
    <div class="container">

        <a href="index.php" class="back-link">⬅ Back to Dashboard</a>

        <!-- HEADER DIAL PROFILE -->
        <div class="profile-header fade-up">
            <div class="avatar-big"><?= $initial ?></div>
            <div class="profile-info">
                <h2><?= htmlspecialchars($name) ?></h2>
                <p>📧 <?= htmlspecialchars($email) ?></p>
                <span
                    style="display:inline-block; background:rgba(255,255,255,0.15); padding:6px 16px; border-radius:20px; font-size:0.85rem; font-weight:600; border:1px solid rgba(255,255,255,0.2);">🎓
                    Student</span>
            </div>
        </div>

        <!-- MA3LOUMAT IDAFIYA -->
        <div class="section fade-up delay-1">
            <div class="section-title">📋 Personal Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Phone Number</span>
                    <span class="info-value">📞 <?= htmlspecialchars($phone) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Group</span>
                    <span class="info-value">👥 <?= htmlspecialchars($group_name) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Member Since</span>
                    <span class="info-value">📅 <?= $joined_date ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Role</span>
                    <span class="info-value">🎓 Student</span>
                </div>
            </div>
        </div>

        <!-- IHSAAIYAT -->
        <div class="section fade-up delay-2">
            <div class="section-title">📊 Academic Statistics</div>
            <div class="stats-grid" style="margin-bottom:0;">
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-value"><?= $course_count ?></div>
                    <div class="stat-label">Enrolled Courses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-value text-success"><?= $present ?></div>
                    <div class="stat-label">Days Present</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">❌</div>
                    <div class="stat-value text-danger"><?= $absent ?></div>
                    <div class="stat-label">Days Absent</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-value text-warning"><?= $rate ?>%</div>
                    <div class="stat-label">Global Rate</div>
                </div>
            </div>
        </div>

        <!-- THEME SELECTOR (RJA3NAH) -->
        <div style="text-align:center;" class="fade-up delay-3">
            <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:10px;">🎨 Choose Theme</p>
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

    <!-- JS DIAL THEME (RJA3NAH) -->
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