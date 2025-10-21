<?php
session_start();

// Temporary message store
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [
        ["name" => "Rehan", "message" => "Hi! How are you?", "time" => date("h:i A")]
    ];
}

// Auto reply function
function autoReply($text) {
    $text = strtolower($text);
    if (strpos($text, 'hello') !== false) return "Hi there! 😊";
    if (strpos($text, 'how are you') !== false) return "I'm fine! What about you?";
    if (strpos($text, 'fine') !== false) return "That's great to hear!";
    if (strpos($text, 'bye') !== false) return "Goodbye! Take care 👋";
    return "Hmm... interesting! Tell me more 😄";
}

// When message is sent
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['message'])) {
    $msg = htmlspecialchars($_POST['message']);
    $_SESSION['messages'][] = ["name" => "You", "message" => $msg, "time" => date("h:i A")];
    // Auto reply
    $reply = autoReply($msg);
    $_SESSION['messages'][] = ["name" => "Rehan", "message" => $reply, "time" => date("h:i A")];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>WhatsApp Chat Clone</title>
<style>
body {
  font-family: Arial, sans-serif;
  background-color: #ece5dd;
  margin: 0;
  padding: 0;
}
.chat-container {
  width: 400px;
  margin: 40px auto;
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  overflow: hidden;
}
.header {
  background-color: #075E54;
  color: #fff;
  padding: 15px;
  font-size: 18px;
  text-align: center;
}
.messages {
  padding: 10px;
  height: 400px;
  overflow-y: auto;
  background-color: #e5ddd5;
}
.message {
  margin: 8px 0;
  padding: 10px;
  border-radius: 10px;
  max-width: 75%;
  position: relative;
  font-size: 15px;
}
.sender {
  background-color: #dcf8c6;
  margin-left: auto;
}
.receiver {
  background-color: #fff;
}
.time {
  font-size: 11px;
  color: gray;
  text-align: right;
}
.number {
  font-size: 13px;
  color: #128C7E;
  font-weight: bold;
}
.input-area {
  display: flex;
  border-top: 1px solid #ccc;
}
.input-area input {
  flex: 1;
  padding: 10px;
  border: none;
  outline: none;
}
.input-area button {
  background-color: #128C7E;
  color: white;
  border: none;
  padding: 10px 15px;
  cursor: pointer;
}
.input-area button:hover {
  background-color: #0b5c4a;
}
</style>
</head>
<body>

<div class="chat-container">
  <div class="header">WhatsApp Clone 💬</div>
  <div class="messages">
    <?php foreach($_SESSION['messages'] as $msg): ?>
      <div class="message <?php echo ($msg['name'] == 'You') ? 'sender' : 'receiver'; ?>">
        <div class="number">#<?php echo rand(1000,9999); ?></div>
        <b><?php echo $msg['name']; ?>:</b> <?php echo $msg['message']; ?>
        <div class="time"><?php echo $msg['time']; ?></div>
      </div>
    <?php endforeach; ?>
  </div>
  <form method="post" class="input-area">
    <input type="text" name="message" placeholder="Type a message..." required>
    <button type="submit">Send</button>
  </form>
</div>

</body>
</html>
