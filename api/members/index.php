<?php
require_once '../../src/config/Database.php';
require_once '../../src/models/Member.php';
require_once '../../helpers/jwt.php';

header('Content-Type: application/json');

// Get Bearer Token
$token = get_bearer_token();
$user = validate_jwt($token);

// Handle Unauthorized
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$member = new Member();
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

switch ($method) {
    case 'GET':
        $data = $id ? $member->find($id) : $member->all();
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        echo json_encode(['success' => $member->create($input['full_name'])]);
        break;

    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing ID']);
            break;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        echo json_encode(['success' => $member->update($id, $input['full_name'])]);
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing ID']);
            break;
        }
        $db = new Database();
        $stmt = $db->connect()->prepare("DELETE FROM members WHERE id = ?");
        echo json_encode(['success' => $stmt->execute([$id])]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
