<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="initial-scale=1, width=device-width" />
    <title>iniGadget</title>
    <link rel="icon" type="image/x-icon" href="/assets/img/logo-light.svg" />
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400&display=swap" />
</head>

<body>
    <?php include 'header.php'; ?>
    <?php include 'ad-header.php' ?>
    <div class="main">
        <?php 
        if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
            $searchQuery = htmlspecialchars(trim($_GET['query']));
            include 'search.php';
        } else {
            include 'home.php';
        }
        ?>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>