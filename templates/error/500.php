<?php
declare(strict_types=1);

/**
 * @var bool $debug
 * @var \Throwable $exception
 * @var string $message
 * @var int $status
 */
?>

<h2>
    <i class="bi bi-exclamation-triangle"></i> <?= __('Error {0}', $status) ?>
</h2>

<div class="fs-5">
    <?= h($message) ?>
</div>

<?php if ($debug) : ?>
    <hr />

    <div>
        <strong>Exception:</strong>
        <code><?= h($exception::class) ?></code>
    </div>

    <div>
        <strong>File:</strong>
        <code><?= h($exception->getFile()) ?></code>
    </div>

    <div>
        <strong>Line:</strong>
        <code><?= $exception->getLine() ?></code>
    </div>

    <div class="mt-3">
        <h5>Stack trace</h5>
        <pre><?= $exception->getTraceAsString() ?></pre>
    </div>
<?php endif; ?>
