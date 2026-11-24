<?php
declare(strict_types=1);

final class ProfileRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT p.genre, p.age, p.poidsKg AS poids, p.tailleCm AS taille,
                    b.pays AS ancienPays, b.montant AS ancienBudget
             FROM ProfilNutritionnel p LEFT JOIN BudgetProfilNutritionnel b ON b.idProfil = p.idProfil
             WHERE p.idProfil = :id'
        );
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function save(array $profile, ?int $id): int
    {
        if ($id !== null) {
            $statement = $this->pdo->prepare(
                'UPDATE ProfilNutritionnel SET genre = :genre, age = :age, poidsKg = :poids, tailleCm = :taille
                 WHERE idProfil = :id'
            );
            $statement->execute($profile + ['id' => $id]);
            if ($statement->rowCount() > 0 || $this->find($id) !== null) {
                return $id;
            }
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO ProfilNutritionnel (genre, age, poidsKg, tailleCm) VALUES (:genre, :age, :poids, :taille)'
        );
        $statement->execute($profile);
        return (int) $this->pdo->lastInsertId();
    }
}
