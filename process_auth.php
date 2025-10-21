<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if(!$input || !isset($input['action'])) {
    echo json_encode(['success'=>false,'error'=>'Invalid request']);
    exit;
}
$action = $input['action'];

if ($action === 'signup') {
    $display = trim($input['display'] ?? '');
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';
    if (!$display || !$username || !$password) {
        echo json_encode(['success'=>false,'error'=>'All fields required']);
        exit;
    }
    // check username
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(['success'=>false,'error'=>'Username already taken']);
        exit;
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $ins = $pdo->prepare("INSERT INTO users (username,password_hash,display_name) VALUES (?,?,?)");
    $ins->execute([$username,$hash,$display]);
    $uid = $pdo->lastInsertId();
    $_SESSION['user_id'] = $uid;
    echo json_encode(['success'=>true,'user_id'=>$uid]);
    exit;
}

if ($action === 'login') {
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';
    if (!$username || !$password) {
        echo json_encode(['success'=>false,'error'=>'Provide username and password']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT id,password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($password, $row['password_hash'])) {
        echo json_encode(['success'=>false,'error'=>'Invalid credentials']);
        exit;
    }
    $_SESSION['user_id'] = $row['id'];
    // update last_seen
    $pdo->prepare("UPDATE users SET last_seen = NOW() WHERE id = ?")->execute([$row['id']]);
    echo json_encode(['success'=>true,'user_id'=>$row['id']]);
    exit;
}

echo json_encode(['success'=>false,'error'=>'Unknown action']);
