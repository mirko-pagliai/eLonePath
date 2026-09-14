<?php
declare(strict_types=1);

/**
 * @var string $content
 * @var string $message
 * @var int $status
 */
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="manifest" href="/manifest.json">

        <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="/assets/css/bootstrap-icons.min.css">

        <link rel="icon" href="/img/icons/favicon.ico">
        <link rel="apple-touch-icon" sizes="180x180" href="/img/icons/apple-touch-icon.png">

        <title><?= $status ?> - <?= h($message) ?></title>
    </head>
    <body class="bg-body-secondary min-vh-100">
        <main class="mx-auto p-0 pt-lg-5 pb-lg-4" style="max-width: 960px">
            <div class="card border-0 p-4 p-md-5 shadow-sm">
                <?= $content ?>
            </div>
        </main>
    </body>
</html>
