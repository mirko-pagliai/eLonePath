<?php
declare(strict_types=1);

namespace App\Story;

use Elone\Core\LabeledEnum;

/**
 * An enum representing the genre of a story.
 */
enum StoryGenre: string implements LabeledEnum
{
    case SCIENCE_FICTION = 'science_fiction';

    case MEDIEVAL_FANTASY = 'medieval_fantasy';

    /**
     * @inheritDoc
     */
    public function label(): string
    {
        return match ($this) {
            self::SCIENCE_FICTION => 'Science fiction',
            self::MEDIEVAL_FANTASY => 'Medieval fantasy',
        };
    }
}
