<?php
require_once __DIR__ . '/../config.php';

class CategorieConseil
{
    public function __construct(
        public ?int $id = null,
        public string $nom = '',
        public ?string $description = null
    ) {}

    /** @return CategorieConseil[] */
    public static function all(): array
    {
        $rows = Database::getConnection()
            ->query('SELECT id, nom, description FROM categorie_conseil ORDER BY id')
            ->fetchAll();
        return array_map(fn($r) => new self((int) $r['id'], $r['nom'], $r['description']), $rows);
    }

    public static function find(int $id): ?self
    {
        $st = Database::getConnection()->prepare('SELECT id, nom, description FROM categorie_conseil WHERE id = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ? new self((int) $r['id'], $r['nom'], $r['description']) : null;
    }

    /** Ajoute la catégorie, ou la modifie si elle a déjà un id. */
    public function save(): void
    {
        $db = Database::getConnection();
        if ($this->id === null) {
            $db->prepare('INSERT INTO categorie_conseil (nom, description) VALUES (?, ?)')
               ->execute([$this->nom, $this->description]);
            $this->id = (int) $db->lastInsertId();
        } else {
            $db->prepare('UPDATE categorie_conseil SET nom = ?, description = ? WHERE id = ?')
               ->execute([$this->nom, $this->description, $this->id]);
        }
    }

    /** Refusé par la base si la catégorie contient encore des conseils (ON DELETE RESTRICT). */
    public static function delete(int $id): void
    {
        Database::getConnection()->prepare('DELETE FROM categorie_conseil WHERE id = ?')->execute([$id]);
    }

    public static function count(): int
    {
        return (int) Database::getConnection()->query('SELECT COUNT(*) FROM categorie_conseil')->fetchColumn();
    }

    public static function nombreConseils(int $id): int
    {
        $st = Database::getConnection()->prepare('SELECT COUNT(*) FROM conseil WHERE id_categorie = ?');
        $st->execute([$id]);
        return (int) $st->fetchColumn();
    }

    /** Catégories avec le nombre de conseils qu'elles contiennent. */
    public static function avecNombre(): array
    {
        return Database::getConnection()->query(
            'SELECT k.id, k.nom, k.description, COUNT(c.id) AS nb
             FROM categorie_conseil k LEFT JOIN conseil c ON c.id_categorie = k.id
             GROUP BY k.id, k.nom, k.description ORDER BY k.id'
        )->fetchAll();
    }
}
