<?php
declare(strict_types=1);

namespace App\Story\Nodes;

/**
 * The type of story node — what kind of content it holds and how the reader should react.
 */
enum NodeType: string
{
    /**
     * A regular passage: the reader reads the content and picks one of the listed choices to continue.
     */
    case PASSAGE = 'passage';

    /**
     * A dice check: the reader rolls, and the outcome (see `DiceCheck`) decides which node comes next — no choices
     * are shown.
     */
    case DICE = 'dice';

    /**
     * A winning ending. The story stops here — the reader has completed it successfully.
     */
    case VICTORY = 'victory';

    /**
     * A losing ending. The story stops here — the reader has failed.
     */
    case DEFEAT = 'defeat';
}
