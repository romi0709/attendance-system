<?php
include "../config/auth.php";
require_role("professor");
include "../config/database.php";

$message = "";

// Récupérer tous les étudiants
$students = $conn->prepare("SELECT id, fullname FROM users WHERE role='student'");
$students->execute();
$students = $students->fetchAll();

// Récupérer tous les cours du professeur connecté
$prof_id = $_SESSION['user']['id'];
$courses = $conn->prepare("SELECT id, name FROM courses WHERE professor_id=?");
$courses->execute([$prof_id]);
$courses = $courses->fetchAll();

// Traitement du formulaire
if(isset($_POST['enroll'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];

    // Vérifier si déjà inscrit
    $check = $conn->prepare("SELECT * FROM enrollments WHERE student_id=? AND course_id=?");
    $check->execute([$student_id, $course_id]);

    if($check->rowCount() > 0) {
        $message = "<div style='padding:10px;background:#f44336;color:white;border-radius:6px;margin-top:15px;'>Cet étudiant est déjà inscrit dans ce cours.</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
        $stmt->execute([$student_id, $course_id]);
        $message = "<div style='padding:10px;background:#4caf50;color:white;border-radius:6px;margin-top:15px;'>Étudiant inscrit avec succès !</div>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Inscrire un étudiant</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
    /* ===================== THEMES ===================== */
    :root {
        --c1: #6a5af9;
        --c2: #a178ff;
        --text: #fff;
        --card: rgba(255, 255, 255, 0.22);
    }

    body.theme-rose {
        --c1: #ff6fb7;
        --c2: #ff9ed6;
    }

    body.theme-vert {
        --c1: #4caf50;
        --c2: #8cef9a;
    }

    body.theme-turquoise {
        --c1: #00b9a8;
        --c2: #6cf2e3;
    }

    body.theme-noir {
        --c1: #1a1a1a;
        --c2: #444;
    }

    body.theme-gold {
        --c1: #c9a227;
        --c2: #f0d470;
    }

    /* ===================== MAIN ===================== */
    body {
        margin: 0;
        padding: 30px;
        font-family: "Poppins";
        background: linear-gradient(135deg, var(--c1), var(--c2));
        color: var(--text);
        transition: .25s;
    }

    .container {
        width: 90%;
        max-width: 600px;
        margin: auto;
    }

    .card {
        background: var(--card);
        padding: 25px;
        border-radius: 16px;
        backdrop-filter: blur(12px);
        margin-bottom: 20px;
    }

    h1 {
        margin-bottom: 18px;
        text-align: center;
    }

    select {
        padding: 12px;
        border-radius: 10px;
        border: none;
        width: 100%;
        margin-bottom: 15px;
    }

    button {
        padding: 12px 20px;
        background: var(--c1);
        border: none;
        color: white;
        border-radius: 10px;
        cursor: pointer;
        font-weight: bold;
        width: 100%;
        transition: .2s;
    }

    button:hover {
        background: var(--c2);
    }

    a.back {
        color: white;
        text-decoration: none;
        font-weight: bold;
        display: inline-block;
        margin-bottom: 20px;
    }

    a.back:hover {
        opacity: .8;
    }

    /* ===================== THEME SELECTOR ===================== */
    .theme-box {
        text-align: center;
        margin-top: 15px;
    }

    .theme-btn {
        width: 30px;
        height: 30px;
        display: inline-block;
        border-radius: 50%;
        border: none;
        margin: 5px;
        cursor: pointer;
    }
    </style>

    <script>
    // Charger thème
    document.addEventListener("DOMContentLoaded", () => {
        let t = localStorage.getItem("enroll_theme");
        if (t) document.body.className = t;
    });
    // Changer thème
    function setTheme(t) {
        document.body.className = t;
        localStorage.setItem("enroll_theme", t);
    }
    </script>
</head>

<body>

    <div class="container">

        <a class="back" href="index.php">⬅ Retour</a>

        <h1>📌 Inscrire un étudiant dans un cours</h1>

        <?= $message ?>

        <div class="card">
            <form method="POST">
                <label>Étudiant :</label>
                <select name="student_id" required>
                    <option value="">-- Sélectionner un étudiant --</option>
                    <?php foreach($students as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['fullname']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Cours :</label>
                <select name="course_id" required>
                    <option value="">-- Sélectionner un cours --</option>
                    <?php foreach($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" name="enroll">Inscrire</button>
            </form>
        </div>

        <!-- Theme Selector -->
        <div class="theme-box">
            <h3>🎨 Thème</h3>
            <button class="theme-btn" style="background:#6a5af9" onclick="setTheme('')"></button>
            <button class="theme-btn" style="background:#ff6fb7" onclick="setTheme('theme-rose')"></button>
            <button class="theme-btn" style="background:#4caf50" onclick="setTheme('theme-vert')"></button>
            <button class="theme-btn" style="background:#00b9a8" onclick="setTheme('theme-turquoise')"></button>
            <button class="theme-btn" style="background:#000" onclick="setTheme('theme-noir')"></button>
            <button class="theme-btn" style="background:#c9a227" onclick="setTheme('theme-gold')"></button>
        </div>

    </div>

</body>

</html>