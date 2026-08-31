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
    <hr>

    <p>
        <strong>Exception:</strong>
        <?= h($exception::class) ?>
    </p>

    <p>
        <strong>File:</strong>
        <?= h($exception->getFile()) ?>
    </p>

    <p>
        <strong>Line:</strong>
        <?= $exception->getLine() ?>
    </p>

    <h2>Stack trace</h2>

    <pre><?= h($exception->getTraceAsString()) ?></pre>
<?php endif; ?>
