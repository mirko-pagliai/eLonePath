<?php
declare(strict_types=1);

/**
 * @var \App\Story\Game $game
 * @var \App\Story\Node $node
 * @var \Elone\Core\View\View $this
 *
 * @link \App\Controller\StoryController::chapter()
 */

use App\Story\DefeatNode;
use App\Story\DiceNode;
use App\Story\PassageNode;
use App\Story\VictoryNode;
?>

    <header class="mb-5 text-end">
        <h4 id="story-title" class="elone-title m-0"><?= htmlspecialchars($game->title) ?></h4>

        <div id="story-page" class="story-page fs-5">Pagina <?= $node->id ?></div>
    </header>

<?php if ($node->image !== null) : ?>
    <?= $this->Html->image(
        path: "/assets/stories/$game->gameId/img/{$node->image['path']}",
        attributes: [
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
                params: ['controller' => 'Story', 'action' => 'chapter', $game->gameId, $choice->target],
                attributes: ['class' => 'elone-button fw-medium p-2 fs-5 text-decoration-none'],
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
            text: 'Lancia i dadi',
            params: ['controller' => 'Story', 'action' => 'roll', $game->gameId, $node->id],
            attributes: ['class' => 'elone-button d-inline-block px-3 py-2 text-decoration-none'],
        ) ?>
    </div>
<?php endif; ?>

<?php if ($node instanceof VictoryNode || $node instanceof DefeatNode) : ?>
    <div class="story-result mt-5">
        <?php if ($node instanceof VictoryNode) : ?>
            <div id="story-victory" class="p-3">
                <p class="fs-3 mb-4">Hai vinto!</p>

                <a href="/" class="elone-button d-inline-block px-3 py-2 text-decoration-none">
                    Torna alla homepage
                </a>
            </div>
        <?php else : ?>
            <div id="story-defeat" class="p-3">
                <p class="fs-2 fst-italic mb-3">La tua vita finisce qui.</p>

                <p class="fs-3 mb-4">Hai perso!</p>

                <?= $this->Html->link(
                    text: 'Ricomincia da pagina 1',
                    params: ['controller' => 'Story', 'action' => 'chapter', $game->gameId, 1],
                    attributes: ['class' => 'elone-button d-inline-block px-3 py-2 text-decoration-none'],
                ) ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>