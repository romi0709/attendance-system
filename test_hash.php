<?php
$hash = '$2y$10$QeJY5XNVpkOf0iQSsSN9UOB0d8GloaK3G2Yyp9c8T9VszXkXA3DPG';
$plain = 'admin123';

if (password_verify($plain, $hash)) {
    echo "Password OK!";
} else {
    echo "Password FAILED!";
}
?>