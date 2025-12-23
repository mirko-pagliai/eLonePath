<?php
declare(strict_types=1);

namespace eLonePath;

use InvalidArgumentException;
use RuntimeException;

/**
 * Character class.
 *
 * Represents a player character with stats and inventory management.
 */
class Character
{
    public string $name {
        set {
            if (trim($value) === '') {
                throw new InvalidArgumentException('Character name cannot be empty');
            }
            $this->name = trim($value);
        }
    }

    public Sex $sex;

    /**
     * Current SKILL value
     */
    public int $skill {
        set {
            if ($value < 0) {
                throw new InvalidArgumentException('Skill cannot be negative');
            }
            $this->skill = min($value, $this->maxSkill);
        }
    }

    /**
     * Maximum SKILL value (initial rolled value)
     */
    public int $maxSkill {
        set {
            if ($value < 1) {
                throw new InvalidArgumentException('Max skill must be at least 1');
            }
            $this->maxSkill = $value;
        }
    }

    /**
     * Current STAMINA value
     */
    public int $stamina {
        set {
            if ($value < 0) {
                $this->stamina = 0;
            } else {
                $this->stamina = min($value, $this->maxStamina);
            }
        }
    }

    /**
     * Maximum STAMINA value (initial rolled value)
     */
    public int $maxStamina {
        set {
            if ($value < 1) {
                throw new InvalidArgumentException('Max stamina must be at least 1');
            }
            $this->maxStamina = $value;
        }
    }

    /**
     * Current LUCK value
     */
    public int $luck {
        set {
            if ($value < 0) {
                $this->luck = 0;
            } else {
                $this->luck = min($value, $this->maxLuck);
            }
        }
    }

    /**
     * Maximum LUCK value (initial rolled value)
     */
    public int $maxLuck {
        set {
            if ($value < 1) {
                throw new InvalidArgumentException('Max luck must be at least 1');
            }
            $this->maxLuck = $value;
        }
    }

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
     * @param int $skill Initial SKILL value
     * @param int $stamina Initial STAMINA value
     * @param int $luck Initial LUCK value
     * @param int $gold Initial gold amount
     */
    public function __construct(
        string $name,
        Sex $sex,
        int $skill,
        int $stamina,
        int $luck,
        int $gold = 10
    ) {
        $this->name = $name;
        $this->sex = $sex;
        $this->maxSkill = $skill;
        $this->skill = $skill;
        $this->maxStamina = $stamina;
        $this->stamina = $stamina;
        $this->maxLuck = $luck;
        $this->luck = $luck;
        $this->gold = $gold;
    }

    /**
     * Create a character with randomly rolled stats.
     *
     * @param string $name Character name
     * @param \eLonePath\Sex $sex Character sex
     * @param int $gold Initial gold amount
     * @return self
     * @throws \Random\RandomException
     */
    public static function createWithRolledStats(
        string $name,
        Sex $sex,
        int $gold = 10
    ): self {
        return new self(
            name: $name,
            sex: $sex,
            skill: Dice::rollInitialSkill(),
            stamina: Dice::rollInitialStamina(),
            luck: Dice::rollInitialLuck(),
            gold: $gold,
        );
    }

    /**
     * Generate a completely random character (name, sex, and stats)
     *
     * @param int $gold Initial gold amount
     * @return self
     * @throws \RuntimeException If character_names.json cannot be loaded
     */
    public static function generateRandom(int $gold = 10): self
    {
        // Load character names
        $namesFile = __DIR__ . '/../../character_names.json';
        if (!file_exists($namesFile)) {
            throw new RuntimeException('Character names file not found');
        }

        $namesData = json_decode(file_get_contents($namesFile) ?: '', true);
        if ($namesData === null) {
            throw new RuntimeException('Failed to parse character names JSON');
        }

        // Pick random sex
        $sex = Sex::cases()[array_rand(Sex::cases())];

        // Pick a random name based on sex
        $namesList = $namesData[$sex->value] ?? [];
        if (empty($namesList)) {
            throw new \RuntimeException("No names available for sex: {$sex->value}");
        }
        $name = $namesList[array_rand($namesList)];

        return self::createWithRolledStats($name, $sex, $gold);
    }

    /**
     * Check if the character is alive.
     *
     * @return bool
     */
    public function isAlive(): bool
    {
        return $this->stamina > 0;
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
     * Test LUCK (roll 2d6 <= current LUCK)
     *
     * @return bool True if lucky
     * @throws \Random\RandomException
     */
    public function testLuck(): bool
    {
        $roll = Dice::roll2D6();
        $lucky = $roll <= $this->luck;

        // Decrease luck by 1 after test
        $this->luck--;

        return $lucky;
    }

    /**
     * Export character data to array (for session storage)
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'sex' => $this->sex->value,
            'skill' => $this->skill,
            'maxSkill' => $this->maxSkill,
            'stamina' => $this->stamina,
            'maxStamina' => $this->maxStamina,
            'luck' => $this->luck,
            'maxLuck' => $this->maxLuck,
            'inventory' => $this->inventory,
            'gold' => $this->gold,
        ];
    }

    /**
     * Create character from array data (from session storage)
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $character = new self(
            name: $data['name'],
            sex: Sex::from($data['sex']),
            skill: $data['maxSkill'],
            stamina: $data['maxStamina'],
            luck: $data['maxLuck'],
            gold: $data['gold']
        );

        // Set current values (might be different from max)
        $character->skill = $data['skill'];
        $character->stamina = $data['stamina'];
        $character->luck = $data['luck'];
        $character->inventory = $data['inventory'];

        return $character;
    }
}
