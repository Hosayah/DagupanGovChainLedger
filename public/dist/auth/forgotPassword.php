<?php
session_start();
include("../../../config/config.php");

$msg = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (!isset($_SESSION['verified_email'])) {
    $msg = "❌ No verified session found. Please verify your email first.";
  } else {
    $email = $_SESSION['verified_email'];
    $password = trim(preg_replace('/\s+/', ' ', $_POST["password"]));
    $confirm = trim(preg_replace('/\s+/', ' ', $_POST["confirm"]));
    $hash = password_hash($password, PASSWORD_BCRYPT);

    if (strlen($password) < 6 || preg_match('/\s+/', $password)) {
      $msg = "❌ New password must be at least 6 characters and contain no spaces.";
    } elseif ($password !== $confirm) {
      $msg = "❌ Passwords do not match.";
    } else {
      $update = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
      $update->bind_param("ss", $hash, $email);
      if ($update->execute()) {
        $msg = "✅ Password updated successfully!";
      } else {
        $msg = "❌ Database update failed.";
      }
      $update->close();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Reset Password</title>
  <link href="../../src/output.css" rel="stylesheet">
  <style>
    .croc-bg { background-color: transparent; justify-content: center; display: flex; }
    .ron { height: 100dvh; }
    #croc { border: 3px solid black; background-color: white; width: 7rem; height: 7rem; border-radius: 100px; }
    button:hover { cursor: pointer; }
    .floating-shapes { position: fixed; inset: 0; overflow: hidden; z-index: 0; pointer-events: none; }
    .floating-shapes span { position: absolute; display: block; border-radius: 9999px; opacity: 0.15; filter: blur(0.5px); will-change: transform; }
    @keyframes drift1 { 0%,100% { transform: translateY(0) translateX(0) rotate(0deg);} 50% { transform: translateY(-20px) translateX(10px) rotate(10deg);} }
    @keyframes drift2 { 0%,100% { transform: translateY(0) translateX(0) rotate(0deg);} 50% { transform: translateY(25px) translateX(-15px) rotate(-12deg);} }
    @keyframes drift3 { 0%,100% { transform: translateY(0) translateX(0) rotate(0deg);} 50% { transform: translateY(-30px) translateX(20px) rotate(8deg);} }
  </style>
</head>
<body class="ron bg-gray-100 flex justify-center items-center">
  <div class="floating-shapes" aria-hidden="true">
    <span style="background:#34d399; width:220px; height:220px; left:-60px; top:12%; animation: drift1 14s ease-in-out infinite;"></span>
    <span style="background:#10b981; width:160px; height:160px; right:-40px; top:34%; animation: drift2 18s ease-in-out infinite;"></span>
    <span style="background:#22c55e; width:260px; height:260px; left:18%; bottom:-90px; animation: drift3 22s ease-in-out infinite;"></span>
  </div>

  <div class="" style="position: relative; z-index: 1;">
    <div class="w-screen mx-4 flex justify-center">
      <div class="bg-white shadow-lg rounded-xl">
        <div class="flex gap-6 items-stretch">
          <div class="w-48 flex-shrink-0 flex flex-col items-center justify-center text-center bg-green-600 p-3">
            <h1 class="text-white text-3xl font-bold mb-3">RESET</h1>
            <div class="croc-bg">
              <img src="../assets/images/normal.png" alt="" id="croc">
            </div>
          </div>

          <!-- Right column -->
          <div class="flex-1 min-w-0 p-6">
            <a href="../../index.php">
              <img src="../assets/images/logo/logo2.jpg" alt="Dagupan GovChain Ledger" 
                   style="width: auto; height: 96px; border-radius: 10px; object-fit: cover;" />
            </a>

            <?php if (!empty($msg)): ?>
              <p id="msg" class="text-sm mb-3 <?= strpos($msg, '✅') !== false ? 'text-green-600' : 'text-red-600' ?>">
                <?= htmlspecialchars($msg, ENT_QUOTES) ?>
              </p>
            <?php endif; ?>

            <form id="resetForm" method="POST" class="space-y-4">
              <!-- New Password -->
              <div class="relative w-full">
                <input id="password" type="password" name="password" placeholder="New Password" required
                       class="w-full p-3 pr-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400" />
                <button id="togglePassword" type="button" class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-green-600">
                  <img src="../assets/images/eye.svg" alt="" class="eye" style="width: 20px; height: 20px;">
                </button>
              </div>

              <!-- Confirm Password -->
              <div class="relative w-full">
                <input id="password1" type="password" name="confirm" placeholder="Repeat Password" required
                       class="w-full p-3 pr-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400" />
                <button id="togglePassword1" type="button" class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-green-600">
                  <img src="../assets/images/eye.svg" alt="" class="eye" style="width: 20px; height: 20px;">
                </button>
              </div>

              <button type="submit" class="w-full bg-green-600 text-white p-3 rounded-lg hover:bg-green-700">
                Reset Password
              </button>
            </form>

            <p class="mt-4 text-sm text-gray-600 text-center">
              <a href="./login.php" class="text-green-600 hover:underline">Proceed to Login</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
  // Password visibility toggle logic (same as before)
  (function() {
    const croc = document.getElementById('croc');
    const pwd = document.getElementById('password');
    const pwd1 = document.getElementById('password1');
    const toggle = document.getElementById('togglePassword');
    const toggle1 = document.getElementById('togglePassword1');
    const imgs = {
      neutral: '../assets/images/normal.png',
      covered: '../assets/images/cover.png',
      peeking: '../assets/images/peek.png'
    };
    let isShowing = false, isShowing1 = false;
    function setCroc(state) {
      if (!imgs[state]) return;
      croc.style.transition = 'opacity 120ms ease';
      croc.style.opacity = '0';
      setTimeout(() => { croc.src = imgs[state]; croc.style.opacity = '1'; }, 120);
    }
    setCroc('neutral');
    function updateCrocOnBlur() {
      const active = document.activeElement;
      if (active !== pwd && active !== pwd1) setCroc('neutral');
    }
    [pwd, pwd1].forEach((el, i) => {
      el.addEventListener('focus', () => setCroc((i ? isShowing1 : isShowing) ? 'peeking' : 'covered'));
      el.addEventListener('blur', () => setTimeout(updateCrocOnBlur, 100));
    });
    [toggle, toggle1].forEach((t, i) => {
      const el = i ? pwd1 : pwd;
      let state = i ? isShowing1 : isShowing;
      const icon = t.querySelector('img');
      t.addEventListener('mousedown', e => e.preventDefault());
      t.addEventListener('click', () => {
        state = !state;
        if (state) {
          el.type = 'text'; icon.src = '../assets/images/eye-crossed.svg'; setCroc('peeking');
        } else {
          el.type = 'password'; icon.src = '../assets/images/eye.svg'; updateCrocOnBlur();
        }
        el.focus();
        if (i) isShowing1 = state; else isShowing = state;
      });
    });
  })();
  </script>
</body>
</html>
