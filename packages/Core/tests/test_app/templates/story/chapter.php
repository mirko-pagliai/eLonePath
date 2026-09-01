<?php
declare(strict_types=1);

/**
 * @var \App\Story\Game $game
 * @var \App\Story\Nodes\Node $node
 * @var \Elone\Core\View\View $this
 *
 * @link \App\Controller\StoryController::chapter()
 */

use App\Story\Nodes\DefeatNode;
use App\Story\Nodes\DiceNode;
use App\Story\Nodes\PassageNode;
use App\Story\Nodes\VictoryNode;
?>

    <header class="mb-5 text-end">
        <?= $this->element('story-title', ['game' => $game]) ?>

        <div id="story-page" class="story-page fs-5">Pagina <?= $node->id ?></div>
    </header>

<?php if ($node->image !== null) : ?>
    <?= $this->Html->image(
        path: "/assets/stories/$game->gameId/img/{$node->image['path']}",
        options: [
            'alt' => $node->image['title'],
            'class' => 'img-fluid mx-auto mb-5 d-block',
        ],
    ) ?>
<?php endif ?>

    <section id="story-content" class="fs-4 mb-4">
        <?= $node->content ?>
    </section>

<?php if ($node instanceof PassageNode) : ?>
    <nav id="story-choices" class="d-flex flex-column gap-2">
        <?php
        foreach ($node->choices as $choice) {
            echo $this->Html->link(
                text: $choice->content,
                url: ['controller' => 'Story', 'action' => 'chapter', $game->gameId, $choice->target],
                options: ['class' => 'elone-button fw-medium p-2 fs-5 text-decoration-none'],
            );
        }
        ?>
    </nav>
<?php endif; ?>

<?php if ($node instanceof DiceNode) : ?>
    <div id="story-dice" class="mt-4 text-center">
        <p class="fs-5">
            Lancia <?= $node->requiredRolls ?> <?= $node->requiredRolls === 1 ? 'dado' : 'dadi' ?>:
            servono almeno <?= $node->minimum ?> per superare la prova.
        </p>

        <?= $this->Html->link(
            text: $this->Html->icon('dice-6') . ' Lancia i dadi',
            url: ['controller' => 'Story', 'action' => 'roll', $game->gameId, $node->id],
            options: [
                'class' => 'elone-button d-inline-block px-3 py-2 text-decoration-none',
                'escape' => false,
            ],
        ) ?>
    </div>
<?php endif; ?>

<?php if ($node instanceof VictoryNode || $node instanceof DefeatNode) : ?>
    <div class="story-result mt-5">
        <?php if ($node instanceof VictoryNode) : ?>
            <div id="story-victory" class="p-3">
                <p class="fs-3 mb-4">Hai vinto!</p>

                <?= $this->Html->link(
                    text: 'Torna alla homepage',
                    url: '/',
                    options: ['class' => 'elone-button d-inline-block px-3 py-2 text-decoration-none'],
                ) ?>
            </div>
        <?php else : ?>
            <div id="story-defeat" class="p-3">
                <p class="fs-2 fst-italic mb-3">La tua vita finisce qui.</p>

                <p class="fs-3 mb-4">Hai perso!</p>

                <?= $this->Html->link(
                    text: 'Ricomincia da pagina 1',
                    url: ['controller' => 'Story', 'action' => 'chapter', $game->gameId, 1],
                    options: ['class' => 'elone-button d-inline-block px-3 py-2 text-decoration-none'],
                ) ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

