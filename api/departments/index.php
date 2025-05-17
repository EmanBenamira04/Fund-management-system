<?php
require_once __DIR__ . '/../../src/config/Database.php';
require_once __DIR__ . '/../../src/models/Department.php';
require_once __DIR__ . '/../../helpers/jwt.php';

header('Content-Type: application/json');

// ✅ JWT auth
$token = get_bearer_token();
$user = validate_jwt($token);
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = new Database();
$conn = $db->connect();
$departmentModel = new Department();

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

switch ($method) {
    case 'GET':
        $data = $id ? $departmentModel->find($id) : $departmentModel->all();
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        echo json_encode(['success' => $departmentModel->create($input)]);
        break;

    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing ID']);
            break;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        echo json_encode(['success' => $departmentModel->update($id, $input)]);
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing ID']);
            break;
        }
        echo json_encode(['success' => $departmentModel->delete($id)]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
}
