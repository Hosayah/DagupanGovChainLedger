<?php
session_start();
include("../../../config/config.php"); // your DB connection


$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"]);
  $password = trim($_POST["password"]);
  $hash = password_hash($password, PASSWORD_BCRYPT);

  if (empty($email) || empty($password)) {
    $msg = "❌ Please fill in all fields.";
  } else {
    // Prepare statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
      $msg = "❌ User not found.";
    } else {
      $user = $result->fetch_assoc();

      // Check account status
      if ($user['status'] === "pending") {
        $msg = "⚠️ Your account is still pending approval.";
      } elseif ($user['status'] === "rejected") {
        $msg = "❌ Your account was rejected.";
      } elseif ($user['status'] === "suspended") {
        $msg = "⛔ Your account is suspended.";
      } else {
        // Verify password
        if (password_verify($password, $user['password_hash'])) {
          // Store user session
          $_SESSION['user'] = [
            'id' => $user['user_id'],
            'email' => $user['email'],
            'account_type' => $user['account_type'],
            'name' => $user['full_name'],
            'role' => $user['role'],
            'status' => $user['status']
          ];
          $accountType = $user['account_type'] ?? '';
          if ($accountType === 'admin') {
            $stmt2 = $conn->prepare("SELECT access_level FROM admins WHERE user_id = ?");
            $stmt2->bind_param("i", $user['user_id']);
            $stmt2->execute();
            $admin = $stmt2->get_result()->fetch_assoc();
            $_SESSION['user']['access_level'] = $admin['access_level'] ?? 'review_admin';
          }
          $tableName = $accountType === 'agency' ? 'agencies' : 'auditors';
          if ($accountType === 'agency' || $accountType === 'auditor') {
            $stmt2 = $conn->prepare("SELECT wallet_address FROM $tableName WHERE user_id = ?");
            $stmt2->bind_param("i", $user['user_id']);
            $stmt2->execute();
            $org = $stmt2->get_result()->fetch_assoc();
            $_SESSION['user']['wallet_address'] = $org['wallet_address'] ?? '';
          }

          // Redirect based on account type
          if ($user['account_type'] === 'agency') {
            header("Location: ../govagency/dashboard.php");
            exit;
          } elseif ($user['account_type'] === 'auditor') {
            header("Location: ../auditor/dashboard.php");
            exit;
          } elseif ($user['account_type'] === 'admin') {
            header("Location: ../admin/dashboard.php");
            exit;
          } else {
            header("Location: ../citizen/dashboard.php");
            exit;
          }
        } else {
          echo $user['password_hash'];
          $msg = "❌ Invalid password.";
        }
      }
    }

    $stmt->close();
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Login</title>
  <link href="../../src/output.css" rel="stylesheet">
  <style>
    .croc-bg {
      background-color: transparent;

      justify-content: center;
      display: flex;

    }

    button:hover {
      cursor: pointer;
    }

    .ron {
      height: 100dvh;
    }

    #croc {
      border: 3px solid black;
      background-color: white;
      width: 7rem;
      height: 7rem;
      border-radius: 100px;
    }

    /* Floating background shapes */
    .floating-shapes {
      position: fixed;
      inset: 0;
      overflow: hidden;
      z-index: 0;
      pointer-events: none;
    }

    .floating-shapes span {
      position: absolute;
      display: block;
      border-radius: 9999px;
      opacity: 0.15;
      filter: blur(0.5px);
      will-change: transform;
    }

    @keyframes drift1 {
      0% {
        transform: translateY(0) translateX(0) rotate(0deg);
      }

      50% {
        transform: translateY(-20px) translateX(10px) rotate(10deg);
      }

      100% {
        transform: translateY(0) translateX(0) rotate(0deg);
      }
    }

    @keyframes drift2 {
      0% {
        transform: translateY(0) translateX(0) rotate(0deg);
      }

      50% {
        transform: translateY(25px) translateX(-15px) rotate(-12deg);
      }

      100% {
        transform: translateY(0) translateX(0) rotate(0deg);
      }
    }

    @keyframes drift3 {
      0% {
        transform: translateY(0) translateX(0) rotate(0deg);
      }

      50% {
        transform: translateY(-30px) translateX(20px) rotate(8deg);
      }

      100% {
        transform: translateY(0) translateX(0) rotate(0deg);
      }
    }
  </style>
</head>

<body class="ron bg-gray-100 flex justify-center items-center">
  <div class="floating-shapes" aria-hidden="true">
    <span style="background:#34d399; width:220px; height:220px; left:-60px; top:12%; animation: drift1 14s ease-in-out infinite;"></span>
    <span style="background:#10b981; width:160px; height:160px; right:-40px; top:34%; animation: drift2 18s ease-in-out infinite;"></span>
    <span style="background:#22c55e; width:260px; height:260px; left:18%; bottom:-90px; animation: drift3 22s ease-in-out infinite;"></span>
  </div>
  <div class="" style="position: relative; z-index: 1;">
    <div class="w-screen max-w-md mx-4 flex">


      <!-- CARD pulled up so the croc appears attached (no absolute positioning) -->
      <div class=" bg-white shadow-lg rounded-xl">
        <div class="flex gap-6 items-stretch">
          <div class=" w-48 sm:w-64 flex-shrink-0 flex flex-col items-center justify-center text-center bg-green-600" style="padding: 12px;">
            <h1 class="text-white text-3xl font-bold mb-3">Login</h1>
            <div class="croc-bg">
              <img src="../assets/images/normal.png" alt="" id="croc">
            </div>


          </div>
          <!-- Left column: croc + form -->
          <div class="flex-1 min-w-0 p-6">
            <a href="../index.php"> <img src="../assets/images/logo/logo2.jpg" alt="Dagupan GovChain Ledger" style="width: auto; height: 96px; border-radius: 10px; object-fit: cover;" />
            </a>

            <?php if (!empty($msg)): ?>
              <p id="msg" class="text-sm mb-3 <?= strpos($msg, '✅') !== false ? 'text-green-600' : 'text-red-600' ?>">
                <?= htmlspecialchars($msg, ENT_QUOTES) ?>
              </p>
            <?php endif; ?>

            <form id="loginForm" method="POST" class="space-y-4">
              <input id="email" type="email" name="email" placeholder="Email" required
                class="w-full p-3 border rounded-lg focus:outline-none focus:ring" />

              <!-- Password input with inline toggle (eye) inside the input field -->
              <div class="relative w-full" style="position: relative;">
                <input
                  id="password"
                  type="password"
                  name="password"
                  placeholder="Password"
                  required
                  class="w-full p-3 pr-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400" style="padding-right: 2.5rem;" />
                <button
                  id="togglePassword"
                  type="button"
                  aria-pressed="false"
                  aria-label="Show password"
                  class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-green-600 focus:outline-none" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%);">
                  <img src="../assets/images/eye.svg" alt="" class="eye" style="width: 20px; height: 20px;">
                </button>
              </div>
              <button type="submit" class="w-full bg-green-600 text-white p-3 rounded-lg hover:bg-green-700">
                Login
              </button>
            </form>

            <p class="mt-4 text-sm text-gray-600 text-center">
              Don’t have an account?
              <a href="./register.php" class="text-green-600 hover:underline">Register here</a>
            </p>
          </div>

        </div>
      </div>
    </div>
  </div>

  <script>
    function goHome() {
      window.location = '../../index.php';
    }




    (function() {
      // Elements
      const croc = document.getElementById('croc');
      const email = document.getElementById('email');
      const pwd = document.getElementById('password');
      const toggle = document.getElementById('togglePassword');
      const toggleIcon = toggle.querySelector('img');

      // Image paths (relative to this file). Keep these filenames in ../assets/images/
      const imgs = {
        neutral: '../assets/images/normal.png',
        covered: '../assets/images/cover.png',
        peeking: '../assets/images/peek.png'
      };

      // State
      let isShowing = false; // whether password is visible

      // Helper: change croc image
      function setCroc(state) {
        if (!imgs[state]) return;
        // Use a small fade for smoothness
        croc.style.transition = 'opacity 120ms ease';
        croc.style.opacity = '0';
        setTimeout(() => {
          croc.src = imgs[state];
          croc.style.opacity = '1';
        }, 120);
      }

      // Initial
      setCroc('neutral');

      // When password gets focus -> covered (unless already showing)
      pwd.addEventListener('focus', () => {
        if (!isShowing) {
          setCroc('covered');
        } else {
          setCroc('peeking');
        }
      });

      // Helper to evaluate blur state across both fields
      function updateCrocOnBlur() {
        const active = document.activeElement;
        const neitherFocused = active !== email && active !== pwd;
        if (neitherFocused) {
          setCroc('neutral');
          return;
        }
        if (active === pwd) {
          setCroc(isShowing ? 'peeking' : 'covered');
        } else {
          setCroc('neutral');
        }
      }

      // When password loses focus -> always evaluate both fields
      pwd.addEventListener('blur', () => {
        setTimeout(updateCrocOnBlur, 100);
      });

      // When email gains/loses focus -> adjust croc accordingly
      email.addEventListener('focus', () => {
        setCroc('neutral');
      });
      email.addEventListener('blur', () => {
        setTimeout(updateCrocOnBlur, 100);
      });

      // Prevent blur flicker when clicking toggle
      toggle.addEventListener('mousedown', (e) => {
        e.preventDefault();
      });

      // Toggle visibility
      toggle.addEventListener('click', () => {
        isShowing = !isShowing;

        if (isShowing) {
          pwd.type = 'text';
          toggle.setAttribute('aria-pressed', 'true');
          toggle.setAttribute('aria-label', 'Hide password');
          if (toggleIcon) toggleIcon.src = '../assets/images/eye-crossed.svg';
          setCroc('peeking');
        } else {
          pwd.type = 'password';
          toggle.setAttribute('aria-pressed', 'false');
          toggle.setAttribute('aria-label', 'Show password');
          if (toggleIcon) toggleIcon.src = '../assets/images/eye.svg';
          updateCrocOnBlur();
        }

        // Keep focus on the password input
        pwd.focus();
      });
    })();
  </script>
</body>

</html>