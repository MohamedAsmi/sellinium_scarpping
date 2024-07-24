<?php
class SeriesMapper {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function insert(DatabankSeries $series) {
        $stmt = $this->pdo->prepare('INSERT INTO databank_series (count) VALUES (:count)');
        $stmt->execute([':count' => $series->getCount()]);
        $series->setId($this->pdo->lastInsertId());
    }

    public function findAll() {
        $stmt = $this->pdo->query('SELECT * FROM databank_series');
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $seriesList = [];
        foreach ($results as $row) {
            $seriesList[] = new DatabankSeries($row['id'], $row['count']);
        }
        return $seriesList;
    }

    // Additional methods for update, delete, find by id, etc.
}

