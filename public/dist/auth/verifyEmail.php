<?php
session_start();
include("./controller/verifyController.php");

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Verify Email</title>
  <link href="../../src/output.css" rel="stylesheet">
  <style>
    .ron { height: 100dvh; }
    .croc-bg { background-color: transparent; justify-content: center; display: flex; }
    #croc { border: 3px solid black; background-color: white; width: 7rem; height: 7rem; border-radius: 100px; }
    .floating-shapes {
      position: fixed; inset: 0; overflow: hidden; z-index: 0; pointer-events: none;
    }
    .floating-shapes span {
      position: absolute; display: block; border-radius: 9999px; opacity: 0.15; filter: blur(0.5px);
    }
  </style>
</head>
<body class="ron bg-gray-100 flex justify-center items-center">

  <div class="floating-shapes" aria-hidden="true">
    <span style="background:#34d399; width:220px; height:220px; left:-60px; top:12%; animation: drift1 14s ease-in-out infinite;"></span>
  </div>

  <div class="" style="position: relative; z-index: 1;">
    <div class="w-screen mx-4 flex justify-center">
      <div class="bg-white shadow-lg rounded-xl">
        <div class="flex gap-6 items-stretch">
          
          <!-- LEFT PANEL -->
          <div class="w-48 flex-shrink-0 flex flex-col items-center justify-center text-center bg-green-600" style="padding: 12px;">
            <h1 class="text-white text-3xl font-bold mb-3">VERIFY</h1>
            <div class="croc-bg">
              <img src="../assets/images/normal.png" alt="" id="croc">
            </div>
          </div>

          <!-- RIGHT PANEL -->
          <div class="flex-1 min-w-0 p-6">
            <a href="../../index.php">
              <img src="../assets/images/logo/logo2.jpg" alt="Dagupan GovChain Ledger" style="width: auto; height: 96px; border-radius: 10px; object-fit: cover;" />
            </a>

            <?php if (!empty($msg)): ?>
              <p id="msg" class="text-sm mb-3 <?= strpos($msg, '✅') !== false ? 'text-green-600' : 'text-red-600' ?>">
                <?= htmlspecialchars($msg, ENT_QUOTES) ?>
              </p>
            <?php endif; ?>

            <!-- Dynamic form -->
            <form method="POST" class="space-y-4">
              <?php if ($step === 1): ?>
                <input id="email" type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="Enter your registered email" required class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400" />
                <button type="submit" name="action" value="send_otp" class="w-full bg-green-600 text-white p-3 rounded-lg hover:bg-green-700">Send OTP</button>

              <?php else: ?>
                <input type="text" name="otp" placeholder="Enter OTP sent to your email" required class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400" />
                <button type="submit" name="action" value="verify_otp" class="w-full bg-green-600 text-white p-3 rounded-lg hover:bg-green-700">Verify OTP</button>
                <p class="text-sm text-center text-gray-500 mt-2">
                  Didn’t receive it? <button name="action" value="send_otp" class="text-green-600 hover:underline bg-transparent border-none cursor-pointer">Resend OTP</button>
                </p>
              <?php endif; ?>
            </form>

            <p class="mt-4 text-sm text-gray-600 text-center">
              <a href="./login.php" class="text-green-600 hover:underline">Back to Login</a>
            </p>
          </div>

        </div>
      </div>
    </div>
  </div>
</body>
</html>
