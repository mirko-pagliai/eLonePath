<?php
declare(strict_types=1);

namespace eLonePath\Story;

/**
 * Enum for condition types in story choices.
 */
enum ConditionType: string
{
    case COMBAT_WON = 'combat_won';

    case HAS_ITEM = 'has_item';

    case SKILL_GREATER_THAN = 'skill_greater_than';

    case STAMINA_GREATER_THAN = 'stamina_greater_than';

    case LUCK_GREATER_THAN = 'luck_greater_than';
}
