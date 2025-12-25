<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title"><?= htmlspecialchars($title) ?></h2>
                <p class="card-text"><?= htmlspecialchars($message) ?></p>

                <h3 class="mt-4">Items list:</h3>
                <ul class="list-group list-group-flush">
                    <?php foreach ($items as $item): ?>
                        <li class="list-group-item"><?= htmlspecialchars($item) ?></li>
                    <?php endforeach; ?>
                </ul>

                <div class="mt-4">
                    <strong>Date and time:</strong>
                    <span class="badge bg-secondary"><?= date('d/m/Y H:i:s') ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
