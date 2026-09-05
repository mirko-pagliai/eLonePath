<?php
declare(strict_types=1);

namespace App\Story\Nodes;

/**
 * @phpstan-type CombatNodeData array{
 *     content: string,
 *     type: string,
 *     combat: array{
 *         enemy_name: string,
 *         enemy_max_life_points: int,
 *         enemy_strength: int,
 *         enemy_agility: int,
 *         target_victory: int,
 *         target_defeat: int,
 *     },
 * }
 */
class CombatNode extends Node
{
    public function __construct(
        int $id,
        string $gameId,
        string $content,
        protected(set) readonly string $enemyName,
        protected(set) readonly int $enemyMaxLifePoints,
        protected(set) readonly int $enemyStrength,
        protected(set) readonly int $enemyAgility,
        protected(set) readonly int $targetVictory,
        protected(set) readonly int $targetDefeat,
    ) {
        parent::__construct($id, $gameId, $content);
    }

    /**
     * @return CombatNodeData
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'type' => 'combat',
            'combat' => [
                'enemy_name' => $this->enemyName,
                'enemy_max_life_points' => $this->enemyMaxLifePoints,
                'enemy_strength' => $this->enemyStrength,
                'enemy_agility' => $this->enemyAgility,
                'target_victory' => $this->targetVictory,
                'target_defeat' => $this->targetDefeat,
            ],
        ];
    }

    /**
     * @param CombatNodeData $data
     */
    public static function createFromArray(int $id, string $gameId, array $data): CombatNode
    {
        return new self(
            id: $id,
            gameId: $gameId,
            content: $data['content'],
            enemyName: $data['combat']['enemy_name'],
            enemyMaxLifePoints: $data['combat']['enemy_max_life_points'],
            enemyStrength: $data['combat']['enemy_strength'],
            enemyAgility: $data['combat']['enemy_agility'],
            targetVictory: $data['combat']['target_victory'],
            targetDefeat: $data['combat']['target_defeat'],
        );
    }
}
