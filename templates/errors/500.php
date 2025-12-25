<?php
declare(strict_types=1);

/**
 * 500 Internal Server Error.
 *
 * @var int $statusCode
 * @var \Throwable|null $exception
 */
?>

<div class="card-header bg-danger bg-gradient bg-opacity-75 text-white text-center py-5">
    <h2 class="h3 mb-2">🔥 <?= $statusCode ?> - Server Error</h2>
    <p class="lead mb-0">Something went wrong on our end. We're working to fix it!</p>
</div>

<div class="card-body p-4">
    <div class="text-center mb-4">
        <p class="lead fw-semibold">What you can do:</p>
        <ul class="list-unstyled text-start mx-auto" style="max-width: 500px;">
            <li class="mb-2">🔸 Try refreshing the page</li>
            <li class="mb-2">🔸 Go back to the previous page</li>
            <li class="mb-2">🔸 Return to the homepage</li>
            <li class="mb-2">🔸 Contact support if the problem persists</li>
        </ul>
    </div>

    <div class="text-center mb-4">
        <a href="/" class="btn btn-primary btn-lg px-4">← Back to Homepage</a>
    </div>

    <?php if (isset($exception)): ?>
        <div class="alert alert-danger border-1 border-danger border-start m-0">
            <div class="fw-bold mb-3">
                Error message: <span class="fw-lighter"><?= lcfirst(htmlspecialchars($exception->getMessage())) ?></span>
            </div>

            <div class="mb-3">
                <strong class="d-block mb-1">Location:</strong>
                <pre class="bg-light border mb-0 rounded p-3 small" style="white-space: pre-line;">
                    <?= htmlspecialchars($exception->getFile()) ?> on line <?= $exception->getLine() ?>
                </pre>
            </div>

            <div>
                <strong class="d-block mb-2">Stack Trace:</strong>
                <pre class="bg-light border mb-0 rounded p-3 small" style="white-space: pre-line;">
                    <?= htmlspecialchars($exception->getTraceAsString()) ?>
                </pre>
            </div>
        </div>
    <?php endif; ?>
</div>