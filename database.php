<?php

class Database
{
    private $conn;

    public function __construct()
    {
        // Create a new database connection
        $this->conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

        // Check if the connection failed
        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
    }

    /**
     * Check if a record exists in the database
     * 
     * @param string $table The table name
     * @param array $data An associative array where keys are column names and values are the corresponding values to search for
     * @return bool True if the record exists, false otherwise
     */
    public function recordExists($table, array $data): bool
    {
        $query = "SELECT COUNT(*) FROM $table WHERE ";
        $query .= implode(" AND ", array_map(function($key) { return "`$key` = ?"; }, array_keys($data)));
        
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bind_param(str_repeat("s", count($data)), ...array_values($data));

        // Execute query
        $stmt->execute();

        // Bind result variable
        $stmt->bind_result($count);

        // Fetch value
        $stmt->fetch();

        // Close statement
        $stmt->close();

        return $count > 0;
    }

    /**
     * Insert data into the database if the record does not already exist
     * 
     * @param string $table The table name
     * @param array $data An associative array where keys are column names and values are the corresponding values to insert
     */
    public function insertData($table, array $data): void
    {
        if (!$this->recordExists($table, $data)) {
            $query = "INSERT INTO $table (";
            $query .= implode(", ", array_map(function($key) { return "`$key`"; }, array_keys($data)));
            $query .= ") VALUES (";
            $query .= implode(", ", array_fill(0, count($data), "?"));
            $query .= ")";

            $stmt = $this->conn->prepare($query);

            // Check if prepare statement failed
            if ($stmt === false) {
                die('Prepare failed: ' . htmlspecialchars($this->conn->error));
            }

            // Bind parameters
            $stmt->bind_param(str_repeat("s", count($data)), ...array_values($data));

            // Execute query
            $stmt->execute();

            // Check if execute statement failed
            if ($stmt->error) {
                die('Execute failed: ' . htmlspecialchars($stmt->error));
            }

            // Close statement
            $stmt->close();

            echo "\nData inserted successfully.\n";
        } else {
            echo "\nDuplicate record found for data: " . implode(', ', $data) . "\n";
        }
    }

    /**
     * Insert or update data in the database
     * 
     * @param string $table The table name
     * @param array $data An associative array where keys are column names and values are the corresponding values to insert/update
     * @param array $updateFields An associative array of fields to update if the record exists
     */
    public function insertOrUpdateData($table, array $data, array $updateFields): void
    {
        $query = "INSERT INTO $table (";
        $query .= implode(", ", array_map(function($key) { return "`$key`"; }, array_keys($data)));
        $query .= ") VALUES (";
        $query .= implode(", ", array_fill(0, count($data), "?"));
        $query .= ") ON DUPLICATE KEY UPDATE ";
        $query .= implode(", ", array_map(function($key) { return "`$key` = VALUES(`$key`)"; }, array_keys($updateFields)));

        $stmt = $this->conn->prepare($query);

        // Check if prepare statement failed
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($this->conn->error));
        }

        // Bind parameters
        $stmt->bind_param(str_repeat("s", count($data)), ...array_values($data));

        // Execute query
        $stmt->execute();

        // Check if execute statement failed
        if ($stmt->error) {
            die('Execute failed: ' . htmlspecialchars($stmt->error));
        }

        // Close statement
        $stmt->close();

        echo "\nData inserted or updated successfully.\n";
    }

    /**
     * Fetch all data from a table
     * 
     * @param string $table The table name
     * @return array An array of all rows from the table
     */
    public function fetchAllData($table): array
    {
        $query = "SELECT * FROM $table";
        $result = $this->conn->query($query);

        if ($result === false) {
            die('Query failed: ' . htmlspecialchars($this->conn->error));
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Count all data from a table
     * 
     * @param string $table The table name
     * @return int The count of all rows in the table
     */
    public function countAllData($table): int
    {
        $query = "SELECT COUNT(*) as count FROM $table";
        $result = $this->conn->query($query);

        if ($result === false) {
            die('Query failed: ' . htmlspecialchars($this->conn->error));
        }

        $row = $result->fetch_assoc();

        return (int)$row['count'];
    }

    // Close the database connection
    public function __destruct()
    {
        $this->conn->close();
    }
}
?>
