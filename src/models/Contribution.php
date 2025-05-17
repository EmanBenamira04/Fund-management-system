<?php
class Contribution {
    private $conn;

    public function __construct() {
        $this->conn = (new Database())->connect();
    }

    public function create($data) {
        $sql = "INSERT INTO contributions (
            member_id, date, or_number, tithe, hope_channel, clc_building,
            one_offering_clc, one_offering_church, cb, cf, pfm,
            local_church_others, department_id, remarks
        ) VALUES (
            :member_id, :date, :or_number, :tithe, :hope_channel, :clc_building,
            :one_offering_clc, :one_offering_church, :cb, :cf, :pfm,
            :local_church_others, :department_id, :remarks
        )";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    public function update($data) {
        $sql = "UPDATE contributions SET
            member_id = :member_id,
            date = :date,
            tithe = :tithe,
            hope_channel = :hope_channel,
            clc_building = :clc_building,
            one_offering_clc = :one_offering_clc,
            one_offering_church = :one_offering_church,
            cb = :cb,
            cf = :cf,
            pfm = :pfm,
            local_church_others = :local_church_others,
            department_id = :department_id,
            remarks = :remarks
            WHERE id = :id";
    
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }
    
    

    public function all() {
        $stmt = $this->conn->query("SELECT * FROM contributions ORDER BY date DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allWithMembers() {
        $stmt = $this->conn->query("
            SELECT c.*, m.full_name, d.name AS department_name
            FROM contributions c
            JOIN members m ON c.member_id = m.id
            LEFT JOIN departments d ON c.department_id = d.id
            ORDER BY c.date DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function findWithMember($id) {
        $stmt = $this->conn->prepare("
            SELECT c.*, m.full_name, d.name AS department_name
            FROM contributions c
            JOIN members m ON c.member_id = m.id
            LEFT JOIN departments d ON c.department_id = d.id
            WHERE c.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
}
