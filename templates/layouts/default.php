<?php
declare(strict_types=1);
/**
 * @var string|null $title
 * @var string $content
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'My application') ?></title>
    <link href="assets/bootstrap/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/">My App</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="/">Home</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container my-5">
    <?= $content ?>
</main>

<footer class="bg-light text-center py-4 mt-5">
    <div class="container">
        <p class="text-muted mb-0">&copy; <?= date('Y') ?></p>
    </div>
</footer>

<script src="assets/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
