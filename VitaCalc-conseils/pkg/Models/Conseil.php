<?php
require_once __DIR__ . '/../config.php';

class Conseil
{
    public function __construct(
        public ?int $id = null,
        public string $titre = '',
        public string $contenu = '',
        public ?string $datePublication = null,
        public int $idCategorie = 0
    ) {}

    private static function fromRow(array $r): self
    {
        return new self((int) $r['id'], $r['titre'], $r['contenu'], $r['date_publication'], (int) $r['id_categorie']);
    }

    /** @return Conseil[] */
    public static function all(): array
    {
        $rows = Database::getConnection()
            ->query('SELECT id, titre, contenu, date_publication, id_categorie FROM conseil ORDER BY id')
            ->fetchAll();
        return array_map([self::class, 'fromRow'], $rows);
    }

    /** @return Conseil[] */
    public static function byCategorie(int $idCategorie): array
    {
        $st = Database::getConnection()->prepare(
            'SELECT id, titre, contenu, date_publication, id_categorie FROM conseil WHERE id_categorie = ? ORDER BY id'
        );
        $st->execute([$idCategorie]);
        return array_map([self::class, 'fromRow'], $st->fetchAll());
    }

    public static function find(int $id): ?self
    {
        $st = Database::getConnection()->prepare(
            'SELECT id, titre, contenu, date_publication, id_categorie FROM conseil WHERE id = ?'
        );
        $st->execute([$id]);
        $r = $st->fetch();
        return $r ? self::fromRow($r) : null;
    }

    /** Ajoute le conseil, ou le modifie s'il a déjà un id. */
    public function save(): void
    {
        $db   = Database::getConnection();
        $date = $this->datePublication ?: date('Y-m-d');
        if ($this->id === null) {
            $db->prepare('INSERT INTO conseil (titre, contenu, date_publication, id_categorie) VALUES (?, ?, ?, ?)')
               ->execute([$this->titre, $this->contenu, $date, $this->idCategorie]);
            $this->id = (int) $db->lastInsertId();
        } else {
            $db->prepare('UPDATE conseil SET titre = ?, contenu = ?, date_publication = ?, id_categorie = ? WHERE id = ?')
               ->execute([$this->titre, $this->contenu, $date, $this->idCategorie, $this->id]);
        }
    }

    public static function delete(int $id): void
    {
        Database::getConnection()->prepare('DELETE FROM conseil WHERE id = ?')->execute([$id]);
    }

    public static function count(): int
    {
        return (int) Database::getConnection()->query('SELECT COUNT(*) FROM conseil')->fetchColumn();
    }

    public static function countCeMois(): int
    {
        return (int) Database::getConnection()
            ->query("SELECT COUNT(*) FROM conseil WHERE DATE_FORMAT(date_publication, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')")
            ->fetchColumn();
    }

    /** Conseils avec le nom de leur catégorie (jointure), filtrables, du plus récent au plus ancien. */
    public static function listeAvecCategorie(?int $idCategorie = null, ?int $limite = null): array
    {
        $sql = 'SELECT c.id, c.titre, c.date_publication, c.id_categorie, k.nom AS categorie
                FROM conseil c JOIN categorie_conseil k ON k.id = c.id_categorie';
        $params = [];
        if ($idCategorie) {
            $sql .= ' WHERE c.id_categorie = ?';
            $params[] = $idCategorie;
        }
        $sql .= ' ORDER BY c.date_publication DESC, c.id DESC';
        if ($limite) {
            $sql .= ' LIMIT ' . (int) $limite;
        }
        $st = Database::getConnection()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }
}
