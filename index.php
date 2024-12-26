<?php
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Template.php';

$template = Template::getInstance();

ob_start();
if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $searchQuery = htmlspecialchars(trim($_GET['query']));
    $sort = isset($_GET['sort']) ? htmlspecialchars($_GET['sort']) : '';
    include 'templates/search.php';
} else {
    include 'templates/home.php';
}
$content = ob_get_clean();

include 'templates/base.php';