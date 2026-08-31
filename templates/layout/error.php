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
        <title><?= $status ?> <?= h($message) ?></title>
    </head>
    <body>
        <?= $content ?>
    </body>
</html>