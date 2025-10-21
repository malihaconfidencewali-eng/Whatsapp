<?php
// index.php - login/signup combined
require 'db.php';
session_start();
if (isset($_SESSION['user_id'])) {
    // JS redirect (user asked redirection be JS-based). We'll output a small page that uses JS to go to chat.
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Redirecting...</title></head><body>
    <script>localStorage.setItem("welcome_redirect","1"); location.href="chat.php";</script></body></html>';
    exit;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>WhatsApp Clone — Login / Signup</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
/* Internal CSS — polished, clean, responsive */
:root{--accent:#2b9edb;--muted:#f4f7fb;--text:#222}
*{box-sizing:border-box;font-family:Inter,system-ui,Arial}
body{margin:0;background:linear-gradient(180deg,#f8fbff,#ffffff);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.container{width:100%;max-width:920px;background:white;border-radius:14px;box-shadow:0 10px 30px rgba(20,30,50,0.08);display:grid;grid-template-columns:1fr 1fr;overflow:hidden}
.brand{padding:36px;background:linear-gradient(135deg,var(--accent),#3cc3ff);color:white;display:flex;flex-direction:column;justify-content:center;gap:12px}
.brand h1{margin:0;font-size:28px;letter-spacing:-0.4px}
.brand p{margin:0;opacity:0.95}
.forms{padding:30px}
.tab{display:flex;gap:12px;margin-bottom:18px}
.tab button{flex:1;padding:10px;border-radius:8px;border:0;background:var(--muted);cursor:pointer;font-weight:600}
.tab button.active{background:var(--accent);color:white}
.card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 6px 18px rgba(15,25,40,0.03)}
.input{display:flex;flex-direction:column;margin-bottom:12px}
.input label{font-size:13px;margin-bottom:6px;color:#333}
.input input{padding:10px;border-radius:8px;border:1px solid #e6eef8}
.row{display:flex;gap:10px}
.btn{padding:10px 14px;border-radius:8px;border:0;background:var(--accent);color:white;cursor:pointer;font-weight:700}
.small{font-size:13px;color:#666;margin-top:8px}
.footer{font-size:12px;color:#9aa7b8;margin-top:8px}
@media (max-width:800px){.container{grid-template-columns:1fr;max-width:420px}.brand{padding:20px}}
</style>
</head>
<body>
<div class="container">
  <div class="brand">
    <h1>ChatZone</h1>
    <p>Real-time chatting, messages saved, read receipts — made simple.</p>
    <div style="margin-top:auto;font-size:13px;opacity:0.9">Built with PHP & AJAX • No external CSS/JS files</div>
  </div>

  <div class="forms">
    <div class="tab">
      <button id="loginTab" class="active">Login</button>
      <button id="signupTab">Sign up</button>
    </div>

    <div id="loginForm" class="card">
      <div class="input"><label>Username</label><input id="login_username" type="text" autocomplete="username"></div>
      <div class="input"><label>Password</label><input id="login_password" type="password" autocomplete="current-password"></div>
      <div class="row"><button class="btn" id="loginBtn">Log in</button></div>
      <div class="small" id="loginMsg"></div>
    </div>

    <div id="signupForm" class="card" style="display:none">
      <div class="input"><label>Display Name</label><input id="s_display" type="text"></div>
      <div class="input"><label>Username</label><input id="s_username" type="text" autocomplete="username"></div>
      <div class="input"><label>Password</label><input id="s_password" type="password" autocomplete="new-password"></div>
      <div class="row"><button class="btn" id="signupBtn">Create account</button></div>
      <div class="small" id="signupMsg"></div>
    </div>

    <div class="footer">Tip: After signup, you will be redirected automatically to chat page.</div>
  </div>
</div>

<script>
// Tab switching
document.getElementById('loginTab').onclick = () => {
  document.getElementById('loginTab').classList.add('active');
  document.getElementById('signupTab').classList.remove('active');
  document.getElementById('loginForm').style.display='block';
  document.getElementById('signupForm').style.display='none';
};
document.getElementById('signupTab').onclick = () => {
  document.getElementById('signupTab').classList.add('active');
  document.getElementById('loginTab').classList.remove('active');
  document.getElementById('loginForm').style.display='none';
  document.getElementById('signupForm').style.display='block';
};

// AJAX helper
async function postJSON(url, data) {
  const res = await fetch(url, {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify(data)
  });
  return res.json();
}

// Login
document.getElementById('loginBtn').onclick = async () => {
  const u = document.getElementById('login_username').value.trim();
  const p = document.getElementById('login_password').value;
  document.getElementById('loginMsg').textContent = 'Signing in...';
  try {
    const r = await postJSON('process_auth.php',{action:'login',username:u,password:p});
    if(r.success){
      localStorage.setItem('uid', r.user_id);
      // JS redirect as requested
      location.href = 'chat.php';
    } else {
      document.getElementById('loginMsg').textContent = r.error || 'Login failed';
    }
  } catch(e){
    document.getElementById('loginMsg').textContent = 'Network error';
  }
};

// Signup
document.getElementById('signupBtn').onclick = async () => {
  const d = document.getElementById('s_display').value.trim();
  const u = document.getElementById('s_username').value.trim();
  const p = document.getElementById('s_password').value;
  if(!d||!u||!p){ document.getElementById('signupMsg').textContent='All fields required'; return; }
  document.getElementById('signupMsg').textContent = 'Creating...';
  try {
    const r = await postJSON('process_auth.php',{action:'signup',display:d,username:u,password:p});
    if(r.success){
      localStorage.setItem('uid', r.user_id);
      location.href = 'chat.php';
    } else {
      document.getElementById('signupMsg').textContent = r.error || 'Signup failed';
    }
  } catch(e){ document.getElementById('signupMsg').textContent='Network error'; }
};
</script>
</body>
</html>
