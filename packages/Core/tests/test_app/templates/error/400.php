<?php
declare(strict_types=1);

/**
 * @var bool $debug
 * @var \Throwable $exception
 * @var string $message
 * @var int $status
 */
?>

<h1><?= $status ?> <?= h($message) ?></h1>

<?php if ($debug) : ?>
    <p><?= h($exception::class) ?></p>
<?php endif; ?>

