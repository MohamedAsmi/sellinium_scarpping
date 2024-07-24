<?php

class TimeMapper {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function insert(DatabankTime $time) {
        $stmt = $this->pdo->prepare('INSERT INTO databank_times (count) VALUES (:count)');
        $stmt->execute([':count' => $time->getCount()]);
        $time->setId($this->pdo->lastInsertId());
    }

    public function findAll() {
        $stmt = $this->pdo->query('SELECT * FROM databank_times');
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $times = [];
        foreach ($results as $row) {
            $times[] = new DatabankTime($row['id'], $row['count']);
        }
        return $times;
    }

    // Additional methods for update, delete, find by id, etc.
}
