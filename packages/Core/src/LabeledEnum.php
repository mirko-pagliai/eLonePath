<?php
declare(strict_types=1);

namespace Elone\Core;

/**
 * An interface representing an enum with a label.
 */
interface LabeledEnum
{
    /**
     * Returns the label associated with the current instance.
     *
     * @return string The label corresponding to the current instance state.
     */
    public function label(): string;
}
