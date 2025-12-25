<?php
declare(strict_types=1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?= $statusCode ?></title>
    <link href="assets/bootstrap/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <?= $content ?>
</div>
</body>
</html>
