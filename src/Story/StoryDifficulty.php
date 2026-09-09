<?php
declare(strict_types=1);

namespace App\Story;

use Elone\Core\LabeledEnum;

/**
 * An enum representing the difficulty level of a story.
 */
enum StoryDifficulty: string implements LabeledEnum
{
    case VERY_EASY = 'very_easy';

    case EASY = 'easy';

    case MEDIUM = 'medium';

    case DIFFICULT = 'difficult';

    case VERY_DIFFICULT = 'very_difficult';

    /**
     * @inheritDoc
     */
    public function label(): string
    {
        return match ($this) {
            self::VERY_EASY => 'Very easy',
            self::EASY => 'Easy',
            self::MEDIUM => 'Medium',
            self::DIFFICULT => 'Difficult',
            self::VERY_DIFFICULT => 'Very difficult',
        };
    }
}

