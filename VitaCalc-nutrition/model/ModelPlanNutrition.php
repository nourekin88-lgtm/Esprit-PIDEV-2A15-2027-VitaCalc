<?php
declare(strict_types=1);

final class NutritionPlanRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function choice(int $profileId): ?array
    {
        $statement = $this->pdo->prepare('SELECT objectif, activite FROM ChoixNutritionnel WHERE idProfil = ?');
        $statement->execute([$profileId]);
        return $statement->fetch() ?: null;
    }

    public function saveChoice(int $profileId, string $goal, string $activity): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO ChoixNutritionnel (idProfil, objectif, activite)
             VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE objectif = ?, activite = ?'
        );
        $statement->execute([$profileId, $goal, $activity, $goal, $activity]);
    }

    public function diet(string $goal): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT r.idRegime AS id, r.nomRegime AS nom
             FROM RegimeParObjectif a
             INNER JOIN RegimeAlimentaire r ON r.idRegime = a.idRegime
             WHERE a.objectif = ?'
        );
        $statement->execute([$goal]);
        return $statement->fetch() ?: null;
    }

    public function catalogueMap(string $collection): array
    {
        $sql = $collection === 'objectifs'
            ? 'SELECT idObjectif, objectif FROM ObjectifCataloguePersonnel'
            : 'SELECT idRegime, objectif FROM RegimeParObjectif';
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function substitutions(int $id, string $context): array
    {
        $statement = $this->pdo->prepare(
            'SELECT emplacement, aliment, grammes FROM SubstitutionNutritionnelle WHERE idProfil = ? AND contexte = ?'
        );
        $statement->execute([$id, $context]);
        return array_column($statement->fetchAll(), null, 'emplacement');
    }

    public function saveSubstitution(int $id, string $context, string $slot, string $food, int $grams): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO SubstitutionNutritionnelle (idProfil, emplacement, contexte, aliment, grammes)
             VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE contexte = ?, aliment = ?, grammes = ?'
        );
        $statement->execute([$id, $slot, $context, $food, $grams, $context, $food, $grams]);
    }
}
