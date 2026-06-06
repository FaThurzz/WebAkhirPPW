<?php
session_start();

$page_title = "Login ThurzShop";
$active_page = "login";

$currentUser = null;
if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    $currentUser = $_SESSION['user'];
}

include '../includes/header.php';
?>