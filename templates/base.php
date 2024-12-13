<?php
$config = include(__DIR__ . '/../config/config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'iniGadget'; ?></title>
    <link rel="icon" type="image/x-icon" href="/assets/img/logo-light.svg" />
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/ad-header.css">
</head>
<body>
    <?php 
    include __DIR__ . '/header.php';
    if (strpos($_SERVER['REQUEST_URI'], '/login') === false) {
        include __DIR__ . '/ad-header.php';
    }
    ?>
    
    <main class="main">
        <?php echo $content; ?>
    </main>

    <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>