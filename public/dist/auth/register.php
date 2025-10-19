<?php
session_start();
include("../../../config/config.php"); // DB connection file

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_type = $_POST["user_type"] ?? "";
    $name = trim(preg_replace('/\s+/', ' ', $_POST["name"])) ?? null;
    $email = trim(preg_replace('/\s+/', ' ', $_POST["email"]));
    $password = trim(preg_replace('/\s+/', ' ', $_POST["password"]));
    $confirm = trim(preg_replace('/\s+/', ' ', $_POST["confirm"]));
    $contact = trim(preg_replace('/\s+/', ' ', $_POST["contact"]));

    // Extra info
    $officeCode = $_POST["officeCode"] ?? null;
    $fullName = trim(preg_replace('/\s+/', ' ',$_POST["fullName"])) ?? null;
    $position = $_POST["position"] ?? null;
    $govId = $_POST["govId"] ?? null;
    $accreditation = $_POST["accreditation"] ?? null;
    $wallet = $_POST["wallet"] ?? null;

    if (strlen($password) < 6 || preg_match( '/\s+/', $password)) {
        $msg = "❌ New password must be atleast 6 characters and not have whitespaces";
    } elseif ($confirm != $password) {
      $msg = "❌ Password must match";
    } elseif ($contact < 10) {
      $msg = "❌ Contact must be a valid 10 digit ph number";
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
</head>
<body class="bg-gray-100">

  <div class="max-w-3xl mx-auto bg-white shadow-lg rounded-xl p-6 mt-10">
    <h2 class="text-2xl font-bold mb-4 text-center">Register an Account</h2>

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
    <div>
      <!-- Government Agency Form -->
      <form id="agency-form" class="tab-content" method="POST">
        <input type="hidden" name="user_type" value="agency">
        <input type="text" name="name" placeholder="Agency Name" class="w-full p-2 border rounded mb-2" required>
        <input type="text" name="officeCode" placeholder="Office/Department Code" class="w-full p-2 border rounded mb-2" required>
        <input type="email" name="email" placeholder="Official Email" class="w-full p-2 border rounded mb-2" required>
        <input type="text" name="fullName" placeholder="Full Name of Officer" class="w-full p-2 border rounded mb-2" required>
        <div class="flex">
          <input type="text" name="position" placeholder="Position/Role" class="w-full p-2 border rounded mb-2" required>
          <input type="text" name="govId" placeholder="Government Employee ID Number" class="w-full p-2 border rounded mb-2" required>
        </div>
        
        <input type="text" name="contact" placeholder="Contact Number" class="w-full p-2 border rounded mb-2" required>
        <input type="text" name="wallet" placeholder="Enter Wallet Address (Metamask: 0x591YOURwalletAddress99052af541d2f6f6c673)" class="w-full p-2 border rounded mb-2" required>
        <input type="password" name="password" placeholder="Password" class="w-full p-2 border rounded mb-2" required>
        <input type="password" name="confirm" placeholder="Confirm Password" class="w-full p-2 border rounded mb-2" required>
        <button class="w-full bg-green-600 text-white p-2 rounded hover:bg-green-700">Register</button>
      </form>

      <!-- Auditor Form -->
      <form id="auditor-form" class="tab-content hidden" method="POST">
        <input type="hidden" name="user_type" value="auditor">
        <input type="text" name="name" placeholder="Organization Name" class="w-full p-2 border rounded mb-2" required>
        <input type="text" name="officeCode" placeholder="Office/Department Code" class="w-full p-2 border rounded mb-2" required>
        <input type="text" name="accreditation" placeholder="Accreditation/License Number" class="w-full p-2 border rounded mb-2" required>
        <input type="email" name="email" placeholder="Official Email" class="w-full p-2 border rounded mb-2" required>
        <input type="text" name="fullName" placeholder="Representative Name" class="w-full p-2 border rounded mb-2" required>
        <input type="text" name="role" placeholder="Role (Auditor, Investigator...)" class="w-full p-2 border rounded mb-2" required>
        <input type="text" name="contact" placeholder="Contact Number" class="w-full p-2 border rounded mb-2" required>
        <input type="text" name="wallet" placeholder="Enter Wallet Address (Metamask: 0x591YOURwalletAddress99052af541d2f6f6c673)" class="w-full p-2 border rounded mb-2" required>
        <input type="password" name="password" placeholder="Password" class="w-full p-2 border rounded mb-2" required>
        <input type="password" name="confirm" placeholder="Confirm Password" class="w-full p-2 border rounded mb-2" required>
        <button class="w-full bg-green-600 text-white p-2 rounded hover:bg-green-700">Register</button>
      </form>

      <!-- Citizen Form -->
      <form id="citizen-form" class="tab-content hidden" method="POST">
        <input type="hidden" name="user_type" value="citizen">
        <input type="hidden" name="name" value="">
        <input type="text" name="fullName" placeholder="Full Name" class="w-full p-2 border rounded mb-2" required>
        <input type="email" name="email" placeholder="Email Address" class="w-full p-2 border rounded mb-2" required>
        <input type="text" name="contact" placeholder="Contact Number" class="w-full p-2 border rounded mb-2" required>
        <input type="password" name="password" placeholder="Password" class="w-full p-2 border rounded mb-2" required>
        <input type="password" name="confirm" placeholder="Confirm Password" class="w-full p-2 border rounded mb-2" required>
        <button class="w-full bg-green-600 text-white p-2 rounded hover:bg-green-700">Register</button>
      </form>
    </div>

    <p class="mt-4 text-center text-sm text-gray-600">
      Already have an account? <a href="./login.php" class="text-green-600 hover:underline">Login here</a>
    </p>
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
