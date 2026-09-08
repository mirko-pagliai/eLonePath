<?php
declare(strict_types=1);

/**
 * Shows the player's current character.
 *
 * @var \App\Story\Character $character
 */
?>

<aside class="mb-5">
    <a class="elone-button d-inline-block p-2" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
        <i class="bi bi-lightning-fill fs-5"></i> Il tuo personaggio
    </a>

    <div id="collapseExample" class="collapse fs-3 mt-4 mb-2 pb-3" style="border-bottom: 2px solid var(--elone-accent)">
        <ul class="list-unstyled mb-0 row">
            <li class="col-6 col-md-3">Forza: <strong><?= $character->strength ?></strong></li>
            <li class="col-6 col-md-3">Agilità: <strong><?= $character->agility ?></strong></li>
            <li class="col-6 col-md-3">Percezione: <strong><?= $character->perception ?></strong></li>
            <li class="col-6 col-md-3">Volontà: <strong><?= $character->willpower ?></strong></li>
        </ul>

        <p class="mb-0 mt-2">
            Punti Vita: <strong><?= $character->lifePoints ?> / <?= $character->maxLifePoints ?></strong>
        </p>
    </div>
</aside>
