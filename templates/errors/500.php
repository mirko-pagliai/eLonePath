<?php
declare(strict_types=1);

/**
 * @var string $exceptionType
 * @var string $message
 * @var string $file
 * @var int $line
 * @var string $trace
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Internal Server Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary p-4">
    <div class="container p-4 rounded rounded-2 shadow">
        <h2 class="mb-3 text-danger">💥 Exception Occurred</h2>

        <div class="d-block badge bg-danger-subtle fs-6 fw-normal mb-2 p-3 text-danger text-start">
            <?= $exceptionType ?>
        </div>

        <div class="bg-dark-subtle fw-semibold mb-2 px-3 py-2 rounded text-secondary">
            <?= $message ?>
        </div>

        <div class="bg-body-secondary border mb-3 px-3 py-2 rounded">
            <p class="mb-1">
                <span class="badge rounded-1 bg-success me-1">File</span>
                <code><?= $file ?></code>
            </p>
            <p class="mb-0">
                <span class="badge rounded-1 bg-danger me-1">Line</span>
                <code><?= $line ?></code>
            </p>
        </div>

        <h4 class="mb-3">📋 Stack Trace</h4>

        <?= $trace ?>
    </div>
</body>
</html>
