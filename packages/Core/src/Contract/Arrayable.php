<?php
declare(strict_types=1);

namespace Elone\Core\Contract;

/**
 * A class that can export its own state as a plain array.
 */
interface Arrayable
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
