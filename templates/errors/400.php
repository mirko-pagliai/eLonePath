<?php
declare(strict_types=1);

/**
 * 400 Bad Request.
 *
 * @var int $statusCode
 * @var \Throwable|null $exception
 */
?>

<div class="alert alert-warning">
    <h1 class="display-4"><?= $statusCode ?> - Client Error</h1>
    <p class="lead">The request could not be processed.</p>
</div>

<?php if (isset($exception)): ?>
<div class="card mt-4">
    <div class="card-header bg-warning text-dark">
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
