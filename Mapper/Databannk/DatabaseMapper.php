<?php

class DatabaseMapper {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function insert(DatabankDatabase $indicator) {
        $stmt = $this->pdo->prepare('INSERT INTO databank_database (count) VALUES (:count)');
        $stmt->execute([':count' => $indicator->getCount()]);
        $indicator->setId($this->pdo->lastInsertId());
    }

    public function findAll() {
        $stmt = $this->pdo->query('SELECT * FROM databank_database');
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $indicators = [];
        foreach ($results as $row) {
            $indicators[] = new DatabankDatabase($row['id'], $row['count']);
        }
        return $indicators;
    }

    // Additional methods for update, delete, find by id, etc.
}

