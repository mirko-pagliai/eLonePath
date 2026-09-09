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
 * @link \App\Controller\StoryController::fight()
 */

use App\Story\Combat\CombatHit;

/** @link templates/element/chapter_header.php */
echo $this->element(
    name: 'chapter_header',
    data: ['title' => $game->title, 'subtitle' => "Combattimento contro $node->enemyName"],
);

/** @link templates/element/character_sheet.php */
echo $this->element(name: 'character_sheet', data: ['character' => $character]);
?>

<section id="combat-round" class="fs-4 mb-4 text-center">
    <p class="fs-2">
        Tu: <strong><?= $result->playerTotal ?></strong>
        — <?= h($node->enemyName) ?>: <strong><?= $result->enemyTotal ?></strong>
    </p>

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
        <strong><?= $enemy->lifePoints ?> / <?= $enemy->maxLifePoints ?></strong> Punti Vita
    </p>

    <?= $this->Story->link(
        text: 'Continua a combattere',
        url: ['controller' => 'Story', 'action' => 'fight', $game->gameId, $node->id],
        options: ['class' => 'elone-button'],
    ) ?>
</section>
