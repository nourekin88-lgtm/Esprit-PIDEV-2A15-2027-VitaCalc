<?php
declare(strict_types=1);

final class NutritionRepository
{
    private const COLLECTIONS = [
        'regimes' => [
            'table' => 'RegimeAlimentaire', 'id' => 'idRegime', 'name' => 'nomRegime',
            'title' => 'Régimes alimentaires', 'singular' => 'régime alimentaire', 'icon' => 'bowl',
            'intro' => 'Découvrez différentes façons de composer vos repas et les principes qui les caractérisent.',
        ],
        'objectifs' => [
            'table' => 'ObjectifNutritionnel', 'id' => 'idObjectif', 'name' => 'nomObjectif',
            'title' => 'Objectifs nutritionnels', 'singular' => 'objectif nutritionnel', 'icon' => 'target',
            'intro' => 'Explorez des objectifs concrets pour faire évoluer vos habitudes alimentaires.',
        ],
    ];

    private array $fields;

    public function __construct(private PDO $pdo, string $collection)
    {
        $this->fields = self::collections()[$collection];
    }

    public static function collections(): array
    {
        return self::COLLECTIONS;
    }

    private function select(): string
    {
        return "SELECT {$this->fields['id']} AS id, {$this->fields['name']} AS nom, description
                FROM {$this->fields['table']}";
    }

    public function all(string $query): array
    {
        $sql = $this->select();
        $params = [];
        if ($query !== '') {
            $sql .= " WHERE {$this->fields['name']} LIKE ? ESCAPE '!' OR description LIKE ? ESCAPE '!'";
            $term = '%' . strtr($query, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            $params = [$term, $term];
        }
        $statement = $this->pdo->prepare($sql . " ORDER BY {$this->fields['name']}, {$this->fields['id']}");
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public function newest(): array
    {
        return $this->pdo->query($this->select() . " ORDER BY {$this->fields['id']} DESC LIMIT 3")->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM {$this->fields['table']}")->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare($this->select() . " WHERE {$this->fields['id']} = ?");
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public function create(string $name, string $description): void
    {
        $statement = $this->pdo->prepare(
            "INSERT INTO {$this->fields['table']} ({$this->fields['name']}, description) VALUES (?, ?)"
        );
        $statement->execute([$name, $description]);
    }

    public function update(int $id, string $name, string $description): bool
    {
        $statement = $this->pdo->prepare(
            "UPDATE {$this->fields['table']} SET {$this->fields['name']} = ?, description = ?
             WHERE {$this->fields['id']} = ?"
        );
        $statement->execute([$name, $description, $id]);
        return $statement->rowCount() > 0 || $this->find($id) !== null;
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare("DELETE FROM {$this->fields['table']} WHERE {$this->fields['id']} = ?");
        $statement->execute([$id]);
        return $statement->rowCount() > 0;
    }
}
