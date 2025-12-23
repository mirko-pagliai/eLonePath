<?php
declare(strict_types=1);

namespace eLonePath;

/**
 * Enum for character sex
 */
enum Sex: string
{
    case MALE = 'male';

    case FEMALE = 'female';

    case OTHER = 'other';
}
