<?php
require_once __DIR__ . '/../../src/config/Database.php';
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

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

switch ($method) {
    case 'GET':
        $stmt = $id
        ? $conn->prepare("SELECT id, username, email, password, role FROM users WHERE id = ?")
        : $conn->query("SELECT id, username, email, password, role FROM users ORDER BY id ASC");

        if ($id) {
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        $success = $stmt->execute([$input['username'], $input['email'], $input['password'], $input['role']]);        
        echo json_encode(['success' => $success]);
        break;

    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing ID']);
            break;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, role = ? WHERE id = ?");
        $success = $stmt->execute([$input['username'], $input['email'], $input['password'], $input['role'], $id]);        
        echo json_encode(['success' => $success]);
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing ID']);
            break;
        }
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $success = $stmt->execute([$id]);
        echo json_encode(['success' => $success]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
}
