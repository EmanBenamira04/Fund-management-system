<?php
class DepartmentExpense {
    private $conn;

    public function __construct() {
        $this->conn = (new Database())->connect();
    }

    public function all() {
        $sql = "SELECT e.*, m.full_name 
                FROM department_expenses e 
                LEFT JOIN members m ON m.id = e.member_id 
                ORDER BY e.expense_date DESC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO department_expenses 
            (department_name, member_id, purpose, amount, expense_date) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['department_name'],
            $data['member_id'],
            $data['purpose'],
            $data['amount'],
            $data['expense_date']
        ]);
    }
    public function find($id) {
        $stmt = $this->conn->prepare("SELECT * FROM department_expenses WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
}
