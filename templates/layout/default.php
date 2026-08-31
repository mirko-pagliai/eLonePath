<?php
declare(strict_types=1);

/**
 * @var string $content
 */
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="/assets/css/bootstrap-icons.min.css">
        <link rel="stylesheet" href="/css/default.css">
        <link rel="icon" type="image/png" href="/img/favicon.png">
        <title>Elone Path</title>
    </head>
    <body class="min-vh-100">
        <main class="container py-5" style="max-width: 960px">
            <article class="mx-auto p-4 p-md-5">
                <?= $content ?>
            </article>
        </main>
    </body>
</html>