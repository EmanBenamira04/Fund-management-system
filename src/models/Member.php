<?php
class Member {
    private $conn;

    public function __construct() {
        $this->conn = (new Database())->connect();
    }

    public function create($fullName) {
        $stmt = $this->conn->prepare("INSERT INTO members (full_name) VALUES (?)");
        return $stmt->execute([$fullName]);
    }

    public function all() {
        $stmt = $this->conn->query("SELECT * FROM members ORDER BY full_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $stmt = $this->conn->prepare("SELECT * FROM members WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $fullName) {
        $stmt = $this->conn->prepare("UPDATE members SET full_name = ? WHERE id = ?");
        return $stmt->execute([$fullName, $id]);
    }
}
