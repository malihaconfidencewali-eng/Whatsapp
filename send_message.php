<?php
require 'db.php';
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'error'=>'Not logged in']); exit; }
$me = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$chat_id = isset($input['chat_id']) ? (int)$input['chat_id'] : null;
$receiver = isset($input['receiver_id']) ? (int)$input['receiver_id'] : null;
$msg = trim($input['msg'] ?? '');

if(!$receiver || !$msg) { echo json_encode(['success'=>false,'error'=>'Invalid params']); exit; }

// If chat doesn't exist, create one (pair uniqueness enforced in schema)
if(!$chat_id){
  // attempt to find chat
  $s = $pdo->prepare("SELECT id FROM chats WHERE (user1_id=? AND user2_id=?) OR (user1_id=? AND user2_id=?) LIMIT 1");
  $s->execute([$me,$receiver,$receiver,$me]);
  $r = $s->fetch();
  if($r) $chat_id = $r['id'];
  else {
    $ins = $pdo->prepare("INSERT INTO chats (user1_id,user2_id,last_message,last_time) VALUES (?,?,?,NOW())");
    $ins->execute([$me,$receiver,$msg]);
    $chat_id = $pdo->lastInsertId();
  }
}

// insert message
$ins = $pdo->prepare("INSERT INTO messages (chat_id,sender_id,receiver_id,msg,status) VALUES (?,?,?,?, 'sent')");
$ins->execute([$chat_id,$me,$receiver,$msg]);
$mid = $pdo->lastInsertId();

// update chat last_message/time
$upd = $pdo->prepare("UPDATE chats SET last_message = ?, last_time = NOW() WHERE id = ?");
$upd->execute([$msg,$chat_id]);

// Optionally set previous messages status to 'delivered' for receiver (simplified)
$pdo->prepare("UPDATE messages SET status = 'delivered' WHERE chat_id = ? AND receiver_id = ? AND status = 'sent'")->execute([$chat_id, $receiver]);

echo json_encode(['success'=>true,'message_id'=>$mid,'chat_id'=>$chat_id]);
