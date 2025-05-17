<?php
require_once __DIR__ . '/../config/Database.php';

class Department {
    private $conn;

    public function __construct() {
        $this->conn = (new Database())->connect();
    }

    // Get latest amount and percentage per department
    public function all() {
        $sql = "
            SELECT d.id, d.name, da.amount, da.percentage, da.year
            FROM departments d
            LEFT JOIN (
                SELECT a.*
                FROM department_allocations a
                INNER JOIN (
                    SELECT department_id, MAX(year) AS latest_year
                    FROM department_allocations
                    GROUP BY department_id
                ) latest ON a.department_id = latest.department_id AND a.year = latest.latest_year
            ) da ON d.id = da.department_id
            ORDER BY d.name ASC
        ";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($name, $amount, $percentage, $year) {
        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare("INSERT INTO departments (name) VALUES (?)");
            $stmt->execute([$name]);

            $departmentId = $this->conn->lastInsertId();

            $stmt2 = $this->conn->prepare("INSERT INTO department_allocations (department_id, amount, percentage, year, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt2->execute([$departmentId, $amount, $percentage, $year]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

public function update($id, $name, $amount, $percentage, $year) {
    // ✅ Update department name
    $updateDept = $this->conn->prepare("UPDATE departments SET name = ? WHERE id = ?");
    $updateDept->execute([$name, $id]);

    // Check if allocation for that year already exists
    $check = $this->conn->prepare("SELECT id FROM department_allocations WHERE department_id = ? AND year = ?");
    $check->execute([$id, $year]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Update the existing allocation
        $stmt = $this->conn->prepare("UPDATE department_allocations SET amount = ?, percentage = ?, created_at = NOW() WHERE id = ?");
        return $stmt->execute([$amount, $percentage, $existing['id']]);
    } else {
        // Insert new allocation
        $stmt = $this->conn->prepare("INSERT INTO department_allocations (department_id, amount, percentage, year, created_at) VALUES (?, ?, ?, ?, NOW())");
        return $stmt->execute([$id, $amount, $percentage, $year]);
    }
}



    public function find($id) {
        $sql = "
            SELECT d.id, d.name, da.amount, da.percentage, da.year

            FROM departments d
            LEFT JOIN (
                SELECT *
                FROM department_allocations
                WHERE (department_id, year) IN (
                    SELECT department_id, MAX(year)
                    FROM department_allocations
                    GROUP BY department_id
                )
            ) da ON d.id = da.department_id
            WHERE d.id = ?
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM departments WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
