<?php


class CountryMapper {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function insert(DatabankCountry $country) {
        $stmt = $this->pdo->prepare('INSERT INTO databank_countries (name) VALUES (:name)');
        $stmt->execute([':name' => $country->getCount()]);
        $country->setId($this->pdo->lastInsertId());
    }

    public function findAll() {
        $stmt = $this->pdo->query('SELECT * FROM databank_countries');
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $countries = [];
        foreach ($results as $row) {
            $countries[] = new DatabankCountry($row['id'], $row['name']);
        }
        return $countries;
    }

    // Additional methods for update, delete, find by id, etc.
}

// Similar mapper classes for Series and Time

