<?php
declare(strict_types=1);

/**
 * 500 Internal Server Error.
 *
 * @var int $statusCode
 * @var \Throwable|null $exception
 */
?>

<div class="alert alert-danger">
    <h1 class="display-4"><?= $statusCode ?> - Server Error</h1>
    <p class="lead">Something went wrong on our end.</p>
</div>

<?php if (isset($exception)): ?>
    <div class="card mt-4">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">Debug Information</h5>
        </div>
        <div class="card-body">
            <p><strong>Message:</strong> <?= htmlspecialchars($exception->getMessage()) ?></p>
            <p><strong>File:</strong> <?= htmlspecialchars($exception->getFile()) ?>:<?= $exception->getLine() ?></p>
            <hr>
            <h6>Stack Trace:</h6>
            <pre class="bg-light p-3 rounded"><?= htmlspecialchars($exception->getTraceAsString()) ?></pre>
        </div>
    </div>
<?php endif; ?>
