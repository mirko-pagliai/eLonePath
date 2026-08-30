<?php
declare(strict_types=1);

/**
 * @var bool $debug
 * @var \Throwable $exception
 * @var string $message
 * @var int $status
 */
?>

<h1><?= $status ?> <?= htmlspecialchars($message) ?></h1>

<?php if ($debug) : ?>
    <hr>

    <p>
        <strong>Exception:</strong>
        <?= htmlspecialchars($exception::class) ?>
    </p>

    <p>
        <strong>File:</strong>
        <?= htmlspecialchars($exception->getFile()) ?>
    </p>

    <p>
        <strong>Line:</strong>
        <?= $exception->getLine() ?>
    </p>

    <h2>Stack trace</h2>

    <pre><?= htmlspecialchars($exception->getTraceAsString()) ?></pre>
<?php endif; ?>
