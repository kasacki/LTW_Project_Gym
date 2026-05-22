<?php
require_once 'db.php';
$page_css = 'equipment';
checkLogin();

$equipment = $pdo->query("SELECT * FROM equipment")->fetchAll();

include 'views/equipment.view.php';
