<?php
require 'db.php';
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false]); exit; }
$me = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$chat_id = isset($input['chat_id']) ? (int)$input['chat_id'] : null;
if(!$chat_id){ echo json_encode(['success'=>false]); exit; }

// mark messages where I am receiver
$pdo->prepare("UPDATE messages SET status='read' WHERE chat_id=? AND receiver_id=? AND status!='read'")->execute([$chat_id,$me]);
echo json_encode(['success'=>true]);
