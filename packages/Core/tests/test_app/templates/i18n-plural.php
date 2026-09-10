<?php
declare(strict_types=1);

/**
 * Fixture for `ExtractMessagesCommandTest` — ICU plural syntax lives entirely inside the message string itself;
 * this proves it survives extraction untouched, needing no plural-aware handling of its own.
 */

echo __('{count, plural, one {You have one message} other {You have # messages}}');
