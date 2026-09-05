<?php
declare(strict_types=1);

/**
 * Shows the player's current character, when one exists — omitted entirely (see `chapter.php`/`start.php`, which
 * only include this when `$character` isn't `null`) on any page reached without ever going through character
 * creation.
 *
 * @var \App\Story\Character $character
 */
?>

<aside id="character-sheet" class="mb-4 p-3 border rounded">
    <h2 class="fs-5">Il tuo personaggio</h2>

    <ul class="list-unstyled mb-0 row">
        <li class="col-6 col-md-3">Forza: <strong><?= $character->strength ?></strong></li>
        <li class="col-6 col-md-3">Agilità: <strong><?= $character->agility ?></strong></li>
        <li class="col-6 col-md-3">Percezione: <strong><?= $character->perception ?></strong></li>
        <li class="col-6 col-md-3">Volontà: <strong><?= $character->willpower ?></strong></li>
    </ul>

    <p class="mb-0 mt-2">
        Punti Vita: <strong><?= $character->lifePoints ?> / <?= $character->maxLifePoints ?></strong>
    </p>
</aside>
