<?php
require_once __DIR__ . '/../config/Database.php';

function login($username, $password): bool {
    $db = (new Database())->connect();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $password === $user['password']) {
        session_start();
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];
        return true;
    }
    return false;
}

function logout() {
    session_start();
    session_destroy();
}

function currentUser() {
    return $_SESSION['user'] ?? null;
}
