<?php
session_start();
include("../../../config/config.php"); // DB connection file

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $user_type = $_POST["user_type"] ?? "";
  $name = trim($_POST["name"]) ?? null;
  $email = trim($_POST["email"]);
  $password = $_POST["password"];
  $confirm = $_POST["confirm"];
  $contact = trim($_POST["contact"]);

  // Extra info
  $officeCode = $_POST["officeCode"] ?? null;
  $fullName = $_POST["fullName"] ?? null;
  $position = $_POST["position"] ?? null;
  $govId = $_POST["govId"] ?? null;
  $accreditation = $_POST["accreditation"] ?? null;
  $wallet = $_POST["wallet"] ?? null;

  if ($confirm != $password) {
    $msg = "❌ Password must match";
    //header("Location: ./register.php");
  } else {
    // Generate bcrypt hash (compatible with Node.js bcrypt.hashSync)
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    if (empty($email) || empty($password) || empty($fullName)) {
      $msg = "❌ Please fill in all required fields.";
    } else {
      // Check if user already exists
      $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
      $check->bind_param("s", $email);
      $check->execute();
      $checkResult = $check->get_result();

      if ($checkResult->num_rows > 0) {
        $msg = "⚠️ Email already registered.";
      } else {
        // Insert into users
        $stmt = $conn->prepare("
                INSERT INTO users (account_type, email, password_hash, full_name, contact_number, status)
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");
        $stmt->bind_param("sssss", $user_type, $email, $hashedPassword, $fullName, $contact);

        if ($stmt->execute()) {
          $userId = $conn->insert_id;

          // Insert into role-specific table
          if ($user_type === "agency") {
            $agencyStmt = $conn->prepare("
                        INSERT INTO agencies (user_id, agency_name, office_code, position, gov_id_number, wallet_address)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
            $agencyStmt->bind_param("isssss", $userId, $name, $officeCode, $position, $govId, $wallet);
            $agencyStmt->execute();
            $msg = "✅ Agency registered successfully. Please wait for account approval before logging in.";
          } elseif ($user_type === "auditor") {
            $auditorStmt = $conn->prepare("
                        INSERT INTO auditors (user_id, organization_name, office_code, accreditation_number, wallet_address)
                        VALUES (?, ?, ?, ?, ?)
                    ");
            $auditorStmt->bind_param("issss", $userId, $name, $officeCode, $accreditation, $wallet);
            $auditorStmt->execute();
            $msg = "✅ Auditor registered successfully. Please wait for account approval before logging in.";
          } elseif ($user_type === "citizen") {
            $msg = "✅ Citizen registered successfully (auto-approved).";
          } else {
            $msg = "✅ User registered.";
          }
          echo "<script type='text/javascript'>alert('$msg');</script>";
          header("Location: ./login.php");
        } else {
          $msg = "❌ Error registering user: " . $stmt->error;
        }
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link href="../../src/output.css" rel="stylesheet">
  <style>
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

    button:hover {
      cursor: pointer;
    }
  </style>
</head>

<body class="bg-gray-100 flex justify-center items-center h-screen">
  <div class="floating-shapes" aria-hidden="true">
    <span style="background:#34d399; width:220px; height:220px; left:-60px; top:12%; animation: drift1 14s ease-in-out infinite;"></span>
    <span style="background:#10b981; width:160px; height:160px; right:-40px; top:34%; animation: drift2 18s ease-in-out infinite;"></span>
    <span style="background:#22c55e; width:260px; height:260px; left:18%; bottom:-90px; animation: drift3 22s ease-in-out infinite;"></span>
  </div>

  <div class="max-w-3xl bg-white shadow-lg rounded-xl mt-10" style="z-index: 1;">
    <h2 class="text-3xl font-bold text-center bg-green-600 w-full p-6 text-white" style="border-radius:12px 12px 0 0;">Register an Account</h2>

    <div class="p-6">


      <?php if (!empty($msg)): ?>
        <p id="msg" class="text-sm mb-3 <?= strpos($msg, '✅') !== false ? 'text-green-600' : 'text-red-600' ?>">
          <?= $msg ?>
        </p>
      <?php endif; ?>

      <!-- Tabs -->
      <div class="flex justify-center border-b mb-6">
        <button id="agency-tab" class="tab-btn px-4 py-2 font-semibold text-green-600 border-b-2 border-green-600">Government Agency</button>
        <button id="auditor-tab" class="tab-btn px-4 py-2 text-gray-600 hover:text-green-600">Auditor</button>
        <button id="citizen-tab" class="tab-btn px-4 py-2 text-gray-600 hover:text-green-600">Citizen</button>
      </div>

      <!-- Forms Container -->
      <div class="p-6">
    
        <!-- Government Agency Form -->
        <form id="agency-form" class="tab-content" method="POST" novalidate>
          <input type="hidden" name="user_type" value="agency">

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-2">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="agency-name">Agency Name</label>
              <input id="agency-name" type="text" name="name" placeholder="e.g. Department of Transport"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="officeCode-agency">Office / Dept. Code</label>
              <input id="officeCode-agency" type="text" name="officeCode" placeholder="Office code"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1" for="agency-email">Official Email</label>
              <input id="agency-email" type="email" name="email" placeholder="name@agency.gov.ph"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="agency-fullName">Full Name of Officer</label>
              <input id="agency-fullName" type="text" name="fullName" placeholder="Officer's full name"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="agency-position">Position / Role</label>
              <input id="agency-position" type="text" name="position" placeholder="e.g. Director"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="agency-govId">Government Employee ID</label>
              <input id="agency-govId" type="text" name="govId" placeholder="ID number"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="agency-contact">Contact Number</label>
              <input id="agency-contact" type="text" name="contact" placeholder="+63 9xx xxx xxxx"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1" for="agency-wallet">Wallet Address</label>
              <input id="agency-wallet" type="text" name="wallet" placeholder="0x591YOURwalletAddress..."
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
              <p class="text-xs text-gray-500 mt-1">Metamask-compatible address (required).</p>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="agency-password">Password</label>
              <input id="agency-password" type="password" name="password" placeholder="Choose a strong password"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="agency-confirm">Confirm Password</label>
              <input id="agency-confirm" type="password" name="confirm" placeholder="Repeat password"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>
          </div>

          <button type="submit" class="w-full bg-green-600 text-white p-3 rounded-md hover:bg-green-700 transition">Register</button>
        </form>

        <!-- Auditor Form -->
        <form id="auditor-form" class="tab-content hidden" method="POST" novalidate
          style="width: calc(100% + 40px); margin-left: -20px; margin-right: -20px;">
          <input type="hidden" name="user_type" value="auditor">

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-2">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="auditor-name">Organization Name</label>
              <input id="auditor-name" type="text" name="name" placeholder="e.g. Audit Solutions Inc."
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="officeCode-auditor">Office / Dept. Code</label>
              <input id="officeCode-auditor" type="text" name="officeCode" placeholder="Office code"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="auditor-accreditation">Accreditation / License #</label>
              <input id="auditor-accreditation" type="text" name="accreditation" placeholder="Accreditation number"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1" for="auditor-email">Official Email</label>
              <input id="auditor-email" type="email" name="email" placeholder="contact@org.com"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="auditor-fullName">Representative Name</label>
              <input id="auditor-fullName" type="text" name="fullName" placeholder="Representative full name"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="auditor-role">Role</label>
              <input id="auditor-role" type="text" name="role" placeholder="Auditor / Investigator"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="auditor-contact">Contact Number</label>
              <input id="auditor-contact" type="text" name="contact" placeholder="+63 9xx xxx xxxx"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1" for="auditor-wallet">Wallet Address</label>
              <input id="auditor-wallet" type="text" name="wallet" placeholder="0x591YOURwalletAddress..."
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
              <p class="text-xs text-gray-500 mt-1">Metamask-compatible address (required).</p>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="auditor-password">Password</label>
              <input id="auditor-password" type="password" name="password" placeholder="Choose a strong password"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="auditor-confirm">Confirm Password</label>
              <input id="auditor-confirm" type="password" name="confirm" placeholder="Repeat password"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>
          </div>

          <button type="submit" class="w-full bg-green-600 text-white p-3 rounded-md hover:bg-green-700 transition">Register</button>
        </form>

        <!-- Citizen Form -->
        <form id="citizen-form" class="tab-content hidden" method="POST" novalidate
          style="width: calc(100% + 40px); margin-left: -20px; margin-right: -20px;">
          <input type="hidden" name="user_type" value="citizen">
          <input type="hidden" name="name" value="">

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1" for="citizen-fullName">Full Name</label>
              <input id="citizen-fullName" type="text" name="fullName" placeholder="Your full name"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="citizen-email">Email Address</label>
              <input id="citizen-email" type="email" name="email" placeholder="you@example.com"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="citizen-contact">Contact Number</label>
              <input id="citizen-contact" type="text" name="contact" placeholder="+63 9xx xxx xxxx"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="citizen-password">Password</label>
              <input id="citizen-password" type="password" name="password" placeholder="Choose a strong password"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1" for="citizen-confirm">Confirm Password</label>
              <input id="citizen-confirm" type="password" name="confirm" placeholder="Repeat password"
                class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-200" required>
            </div>
          </div>

          <button type="submit" class="w-full bg-green-600 text-white p-3 rounded-md hover:bg-green-700 transition">Register</button>
        </form>


      </div>

      <p class="mt-4 text-center text-sm text-gray-600">
        Already have an account? <a href="./login.php" class="text-green-600 hover:underline">Login here</a>
      </p>
    </div>
  </div>

  <script>
    const tabs = document.querySelectorAll(".tab-btn");
    const contents = document.querySelectorAll(".tab-content");

    tabs.forEach((tab, i) => {
      tab.addEventListener("click", () => {
        tabs.forEach(t => t.classList.remove("text-green-600", "border-b-2", "border-green-600"));
        contents.forEach(c => c.classList.add("hidden"));
        tab.classList.add("text-green-600", "border-b-2", "border-green-600");
        contents[i].classList.remove("hidden");
      });
    });
  </script>

</body>

</html>