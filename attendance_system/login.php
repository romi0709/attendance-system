<?php
session_start();
include "config/database.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);
    $_SESSION['user'] = $user;

    if ($user['role'] === 'admin') header("Location: admin/index.php");
    elseif ($user['role'] === 'professor') header("Location: professor/index.php");
    elseif ($user['role'] === 'student') header("Location: student/index.php");
    exit;
} else {
    $error = "Invalid email or password!";
}
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
    :root {
        --color1: #c86bff;
        --color2: #ff82c8;
    }

    /* THEMES */
    body.theme-rose {
        --color1: #ff6fb7;
        --color2: #ff9ed4;
    }

    body.theme-vert {
        --color1: #4caf50;
        --color2: #81c784;
    }

    body.theme-turquoise {
        --color1: #1abc9c;
        --color2: #48e1c5;
    }

    body.theme-noir {
        --color1: #111;
        --color2: #333;
    }

    body.theme-gold {
        --color1: #c9a227;
        --color2: #f5d85c;
    }

    /* DARK MODE AUTO */
    @media (prefers-color-scheme: dark) {
        body {
            background: #121212 !important;
        }

        .box {
            background: rgba(255, 255, 255, 0.08) !important;
            color: white !important;
        }

        input {
            background: rgba(255, 255, 255, 0.18) !important;
            color: white;
        }
    }

    body {
        margin: 0;
        padding: 0;
        font-family: "Poppins", sans-serif;
        background: linear-gradient(135deg, var(--color1), var(--color2));
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        animation: fade 1.2s linear;
    }

    @keyframes fade {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .box {
        width: 360px;
        padding: 35px;
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(12px);
        border-radius: 20px;
        text-align: center;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
    }

    .logo {
        width: 90px;
        margin-bottom: 10px;
    }

    h1 {
        margin: 0;
        font-size: 26px;
        font-weight: 600;
        color: white;
    }

    .sub {
        margin-bottom: 25px;
        color: #f0eaff;
        font-size: 14px;
    }

    input {
        width: 90%;
        padding: 12px;
        border-radius: 12px;
        border: none;
        margin-top: 15px;
        font-size: 15px;
        background: rgba(255, 255, 255, 0.85);
    }

    button {
        width: 92%;
        padding: 13px;
        border: none;
        border-radius: 12px;
        background: var(--color1);
        color: white;
        cursor: pointer;
        margin-top: 25px;
        font-size: 16px;
        font-weight: bold;
        transition: 0.25s;
    }

    button:hover {
        background: var(--color2);
        transform: translateY(-3px);
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.25);
    }

    .error {
        color: yellow;
        margin-top: 10px;
        font-size: 14px;
    }

    .themes {
        margin-top: 20px;
    }

    .themes button {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        margin: 4px;
    }
    </style>

    <script>
    function setTheme(theme) {
        document.body.className = "theme-" + theme;
    }
    </script>

</head>

<body>

    <div class="box">

        <img src="assets/img/logo.png" class="logo">

        <h1>Université d’Alger 1</h1>
        <p class="sub">Welcome to our attendance system</p>

        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit">Login</button>
        </form>

        <?php if ($error): ?>
        <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <div class="themes">
            <button style="background:#ff6fb7" onclick="setTheme('rose')"></button>
            <button style="background:#4caf50" onclick="setTheme('vert')"></button>
            <button style="background:#1abc9c" onclick="setTheme('turquoise')"></button>
            <button style="background:#111" onclick="setTheme('noir')"></button>
            <button style="background:#c9a227" onclick="setTheme('gold')"></button>
        </div>

    </div>

</body>

</html>