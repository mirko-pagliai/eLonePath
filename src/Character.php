<?php
declare(strict_types=1);

namespace eLonePath;

use eLonePath\Stat\Luck;
use eLonePath\Stat\Skill;
use eLonePath\Stat\Stamina;
use InvalidArgumentException;
use RuntimeException;

/**
 * Character class.
 *
 * Represents a player character with stats and inventory management.
 */
class Character
{
    /**
     * Character name.
     */
    public string $name {
        set {
            if (trim($value) === '') {
                throw new InvalidArgumentException('Character name cannot be empty');
            }
            $this->name = trim($value);
        }
    }

    /**
     * Character sex.
     */
    public Sex $sex;

    /**
     * SKILL stat.
     */
    public Skill $skill;

    /**
     * STAMINA stat.
     */
    public Stamina $stamina;

    /**
     * LUCK stat.
     */
    public Luck $luck;

    /**
     * Character inventory (item names).
     *
     * @var array<string>
     */
    public array $inventory = [];

    /**
     * Gold pieces.
     */
    public int $gold {
        set {
            if ($value < 0) {
                throw new InvalidArgumentException('Gold cannot be negative');
            }
            $this->gold = $value;
        }
    }

    /**
     * Create a new character.
     *
     * @param string $name Character name
     * @param \eLonePath\Sex $sex Character sex
     * @param \eLonePath\Stat\Skill $skill SKILL stat
     * @param \eLonePath\Stat\Stamina $stamina STAMINA stat
     * @param \eLonePath\Stat\Luck $luck LUCK stat
     * @param int $gold Initial gold amount
     */
    public function __construct(
        string $name,
        Sex $sex,
        Skill $skill,
        Stamina $stamina,
        Luck $luck,
        int $gold = 10
    ) {
        $this->name = $name;
        $this->sex = $sex;
        $this->skill = $skill;
        $this->stamina = $stamina;
        $this->luck = $luck;
        $this->gold = $gold;
    }

    /**
     * Retrieves a random character name based on the specified sex.
     *
     * @param \eLonePath\Sex $sex The sex of the character for which to retrieve a name.
     * @return string A randomly selected name corresponding to the specified sex.
     * @throws \RuntimeException If the character names file is not found, cannot be parsed or does not contain names
     *  for the specified sex.
     */
    protected static function getRandomName(Sex $sex): string
    {
        /**
         * Loads character names.
         *
         * @link resources/character_names.json
         */
        $file = RESOURCES . DS . 'character_names.json';
        if (!file_exists($file)) {
            throw new RuntimeException('Character names file not found');
        }

        /** @var array{male: string[], female: string[], other: string[]} $namesData */
        $namesData = json_decode(file_get_contents($file) ?: '', true);
        if ($namesData === null) {
            throw new RuntimeException('Failed to parse character names JSON');
        }

        // Pick a random name based on sex
        $namesBySex = $namesData[$sex->value] ?? [];
        if (empty($namesBySex)) {
            throw new RuntimeException("No names available for sex: {$sex->value}");
        }

        return $namesBySex[array_rand($namesBySex)];
    }

    /**
     * Create an instance with rolled stats, using optional parameters for name, sex, and gold.
     *
     * @param string|null $name Optional name for the instance. If not provided, a random name is generated based on sex.
     * @param \eLonePath\Sex|null $sex Optional sex for the instance. If not provided, a random sex is selected.
     * @param int $gold Initial amount of gold. Defaults to 10.
     * @return self Returns a new instance of the class with rolled stats.
     * @throws \Random\RandomException
     */
    public static function createWithRolledStats(?string $name = null, ?Sex $sex = null, int $gold = 10): self {
        if (!$sex) {
            $sex = Sex::cases()[array_rand(Sex::cases())];
        }

        if (!$name) {
            $name = self::getRandomName($sex);
        }

        return new self(
            name: $name,
            sex: $sex,
            skill: Skill::rollRandom(),
            stamina: Stamina::rollRandom(),
            luck: Luck::rollRandom(),
            gold: $gold,
        );
    }

    /**
     * Check if the character is alive.
     *
     * @return bool
     */
    public function isAlive(): bool
    {
        return $this->stamina->current > 0;
    }

    /**
     * Add an item to the inventory.
     *
     * @param string $item Item name
     * @return void
     */
    public function addItem(string $item): void
    {
        $this->inventory[] = $item;
    }

    /**
     * Remove an item from inventory.
     *
     * @param string $item Item name
     * @return bool True if the item was found and removed
     */
    public function removeItem(string $item): bool
    {
        $key = array_search($item, $this->inventory, true);
        if ($key !== false) {
            unset($this->inventory[$key]);
            $this->inventory = array_values($this->inventory); // Re-index

            return true;
        }

        return false;
    }

    /**
     * Check if the character has an item.
     *
     * @param string $item Item name
     * @return bool
     */
    public function hasItem(string $item): bool
    {
        return in_array($item, $this->inventory, true);
    }

    /**
     * Export character data to array (for session storage).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'sex' => $this->sex->value,
            'skill' => $this->skill->toArray(),
            'stamina' => $this->stamina->toArray(),
            'luck' => $this->luck->toArray(),
            'inventory' => $this->inventory,
            'gold' => $this->gold,
        ];
    }

    /**
     * Create character from array data (from session storage).
     *
     * @param array{name: string, sex: string, skill: array{current: int, max: int}, stamina: array{current: int, max: int}, luck: array{current: int, max: int}, inventory: array<string>, gold: int} $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        /** @var \eLonePath\Stat\Skill $Skill */
        $Skill = Skill::fromArray($data['skill']);
        /** @var \eLonePath\Stat\Stamina $Stamina */
        $Stamina = Stamina::fromArray($data['stamina']);
        /** @var \eLonePath\Stat\Luck $Luck */
        $Luck = Luck::fromArray($data['luck']);

        $character = new self(
            name: $data['name'],
            sex: Sex::from($data['sex']),
            skill: $Skill,
            stamina: $Stamina,
            luck: $Luck,
            gold: $data['gold'],
        );

        $character->inventory = $data['inventory'];

        return $character;
    }
}
