<?php
require 'db.php';
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'error'=>'Not authenticated']); exit; }
$me = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$chat_id = isset($input['chat_id']) ? (int)$input['chat_id'] : null;
$since = $input['since'] ?? null;
$with_id = isset($input['with_id']) ? (int)$input['with_id'] : null;

// If no chat_id provided, can't fetch
if(!$chat_id){
    echo json_encode(['success'=>false,'error'=>'No chat selected']);
    exit;
}

// Fetch messages; if 'since' provided, fetch only new ones
if($since){
  $stmt = $pdo->prepare("SELECT * FROM messages WHERE chat_id = ? AND created_at > ? ORDER BY created_at ASC");
  $stmt->execute([$chat_id,$since]);
} else {
  // load last 100 messages
  $stmt = $pdo->prepare("SELECT * FROM messages WHERE chat_id = ? ORDER BY created_at ASC LIMIT 100");
  $stmt->execute([$chat_id]);
}
$messages = $stmt->fetchAll();
echo json_encode(['success'=>true,'messages'=>$messages]);
