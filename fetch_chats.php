<?php
require 'db.php';
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false]); exit; }
$me = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$q = trim($input['query'] ?? '');

// We need to list chats where user is participant, plus any users not chatted yet (optional). We'll list existing chats + top contacts by username.
$sql = "SELECT c.id as chat_id,
  CASE WHEN c.user1_id = ? THEN c.user2_id ELSE c.user1_id END as with_id,
  u.display_name as name,
  u.username,
  c.last_message,
  DATE_FORMAT(c.last_time,'%Y-%m-%d %H:%i') as last_time,
  (SELECT COUNT(*) FROM messages m WHERE m.chat_id = c.id AND m.receiver_id = ? AND m.status!='read') as unread
FROM chats c
JOIN users u ON u.id = CASE WHEN c.user1_id = ? THEN c.user2_id ELSE c.user1_id END
WHERE c.user1_id = ? OR c.user2_id = ?
ORDER BY c.last_time DESC
LIMIT 50";
$stmt = $pdo->prepare($sql);
$stmt->execute([$me,$me,$me,$me,$me]);
$chats = $stmt->fetchAll();

// optionally filter by search q
if($q){
  $filtered = array_filter($chats, function($c) use ($q){
    return stripos($c['name'],$q)!==false || stripos($c['username'],$q)!==false || stripos($c['last_message'],$q)!==false;
  });
  $chats = array_values($filtered);
}

// If no chats exist, show users as contacts (first 20 others)
if(empty($chats)){
  $us = $pdo->prepare("SELECT id,display_name,username FROM users WHERE id != ? LIMIT 40");
  $us->execute([$me]);
  $rows = $us->fetchAll();
  $chats = [];
  foreach($rows as $r){
    $chats[] = [
      'chat_id' => null,
      'with_id' => (int)$r['id'],
      'name' => $r['display_name'],
      'username' => $r['username'],
      'last_message' => null,
      'last_time' => null,
      'unread' => 0,
      'initial' => strtoupper(substr($r['display_name'],0,1))
    ];
  }
} else {
  // add 'initial' and ensure numeric types
  foreach($chats as &$c){
    $c['initial'] = strtoupper(substr($c['name'],0,1));
    $c['with_id'] = (int)$c['with_id'];
    $c['chat_id'] = (int)$c['chat_id'];
    $c['unread'] = (int)$c['unread'];
  }
}

echo json_encode(['success'=>true,'chats'=>$chats]);
