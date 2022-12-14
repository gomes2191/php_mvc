<!DOCTYPE HTML>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>PHP MVC Framework</title>
   	<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css">
   	<link rel="stylesheet" type="text/css" href="/css/main.css">
    <script src="/js/dark-toggle.js"></script>
</head>
<?php (isset($_COOKIE['darkmode']) && $_COOKIE['darkmode'] === "true") ? $_SESSION['darkmode'] = true : $_SESSION['darkmode'] = false; ?>
<body class="<?= theme('bg-dark text-white-75','bg-white') ?>" <?= !isset($_COOKIE['darkmode']) ? 'onload="loadDarkMode()"' : ''?>>
<div id="darkmode" style="display:none"></div>
<div class="container">
<?php require('nav.php'); ?>
