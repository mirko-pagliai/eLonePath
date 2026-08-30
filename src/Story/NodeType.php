<?php
declare(strict_types=1);

namespace App\Story;

/**
 * Represents the type of node in a system, defined by specific string cases.
 *
 * The available cases are:
 *
 * - STORY: Represents a story-related node.
 * - ENDING: Represents an ending-related node.
 */
enum NodeType: string
{
    case STORY = 'story';

    case ENDING = 'ending';
}
