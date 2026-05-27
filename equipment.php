<?php
require_once 'db.php';
$page_css = 'equipment';
$page_js = 'equipment';
checkLogin();

$equipment = $pdo->query("SELECT * FROM equipment ORDER BY category, name")->fetchAll();

include 'views/equipment.view.php';
