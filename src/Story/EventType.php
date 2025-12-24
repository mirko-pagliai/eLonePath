<?php
declare(strict_types=1);

namespace eLonePath\Story;

/**
 * Enum for event types in story paragraphs.
 */
enum EventType: string
{
    case COMBAT = 'combat';

    case ADD_ITEM = 'add_item';

    case MODIFY_STAT = 'modify_stat';

    case MODIFY_GOLD = 'modify_gold';
}
