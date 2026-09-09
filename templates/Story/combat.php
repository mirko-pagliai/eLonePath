<?php
declare(strict_types=1);

/**
 * @var \App\Story\Character $character
 * @var \App\Story\Enemy $enemy
 * @var \App\Story\Game $game
 * @var \App\Story\Nodes\CombatNode $node
 * @var \App\Story\Combat\CombatRoundResult $result
 * @var \App\View\AppView $this
 *
 * @link \App\Controller\StoryController::combat()
 */

use App\Story\Combat\CombatHit;

/** @link templates/element/chapter_header.php */
echo $this->element(
    name: 'chapter_header',
    data: ['title' => $game->title, 'subtitle' => "Combattimento contro {$node->enemyName}"],
);

/** @link templates/element/character_sheet.php */
echo $this->element(name: 'character_sheet', data: ['character' => $character]);
?>

<section id="combat-round" class="fs-4 mb-4 text-center">
    <div class="d-flex justify-content-center gap-5 mb-4">
        <div>
            <p class="mb-1">Tu</p>

            <?php foreach ($result->playerDice as $roll) : ?>
                <?= $this->Html->icon(
                    name: "dice-$roll",
                    options: ['class' => 'mx-1', 'style' => 'font-size: 3rem'],
                ) ?>
            <?php endforeach; ?>

            <p class="fs-2 mb-0">
                Totale: <strong><?= $result->playerDiceTotal ?></strong>
            </p>
        </div>

        <div>
            <p class="mb-1">Nemico (<em><?= h($node->enemyName) ?>)</em></p>

            <?php foreach ($result->enemyDice as $roll) : ?>
                <?= $this->Html->icon(
                    name: "dice-$roll",
                    options: ['class' => 'mx-1', 'style' => 'font-size: 3rem'],
                ) ?>
            <?php endforeach; ?>

            <p class="fs-2 mb-0">
                Totale: <strong><?= $result->enemyDiceTotal ?></strong>
            </p>
        </div>
    </div>

    <p class="fs-3 fst-italic mb-4">
        <?php if ($result->hit === CombatHit::Player) : ?>
            Colpisci per <strong><?= $result->damage ?></strong> danni!
        <?php elseif ($result->hit === CombatHit::Enemy) : ?>
            Vieni colpito per <strong><?= $result->damage ?></strong> danni.
        <?php else : ?>
            Nessuno dei due colpisce: è una parata.
        <?php endif; ?>
    </p>

    <p class="fs-4 mb-4">
        <?= h($node->enemyName) ?>:
        <strong><?= $enemy->lifePoints ?> / <?= $enemy->maxLifePoints ?></strong> punti vita
    </p>

    <?= $this->Story->link(
        text: $this->Html->icon('bi-crosshair', ['class' => 'me-1']) . 'Continua a combattere',
        url: ['controller' => 'Story', 'action' => 'combat', $game->gameId, $node->id],
        options: ['class' => 'elone-button d-inline-block px-3 py-2'],
    ) ?>
</section>
