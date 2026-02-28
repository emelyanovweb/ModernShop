<?php
// custom.css.php
header('Content-Type: text/css');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

require_once 'config.php';

// Генерируем CSS из настроек
echo generateCustomCSS();
?>