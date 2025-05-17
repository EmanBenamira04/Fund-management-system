<?php
require_once '../../src/config/Database.php';
require_once '../../helpers/jwt.php';

header('Content-Type: application/json');

$token = get_bearer_token();
$user = validate_jwt($token);

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = new Database();
$conn = $db->connect();

$reportType = $_GET['report_type'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

if (!$reportType || !$from || !$to) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

try {
    if ($reportType === 'contribution') {
        $stmt = $conn->prepare("
            SELECT date,
                tithe,
                hope_channel AS hope,
                clc_building AS clc,
                one_offering_clc AS clc_offering,
                one_offering_church AS church_offering,
                cb,
                cf,
                pfm,
                local_church_others AS local_church
            FROM contributions
            WHERE date BETWEEN ? AND ?
            ORDER BY date ASC
        ");
        $stmt->execute([$from, $to]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $results]);
        exit;

    } elseif ($reportType === 'expenses') {
        $stmt = $conn->prepare("
            SELECT e.expense_date AS date, m.full_name, e.purpose, e.amount, e.department_name
            FROM department_expenses e
            LEFT JOIN members m ON m.id = e.member_id
            WHERE e.expense_date BETWEEN ? AND ?
            ORDER BY e.expense_date ASC
        ");
        $stmt->execute([$from, $to]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $results]);
        exit;

    } elseif ($reportType === 'department') {
        $deptId = $_GET['department_id'] ?? null;

        if (!$deptId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing department ID']);
            exit;
        }

        $year = date('Y', strtotime($from));
        $stmt = $conn->prepare("
            SELECT d.name, da.amount, da.percentage, da.year
            FROM department_allocations da
            JOIN departments d ON da.department_id = d.id
            WHERE da.department_id = ? AND da.year = ?
        ");
        $stmt->execute([$deptId, $year]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $data]);
        exit;

    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid report type']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
}
