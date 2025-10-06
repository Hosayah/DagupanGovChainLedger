<?php
session_start();
include("../../../config/config.php"); // DB connection file

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_type = $_POST["user_type"] ?? "";
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $contact = trim($_POST["contact"]);

    // Extra info
    $officeCode = $_POST["officeCode"] ?? null;
    $fullName = $_POST["fullName"] ?? null;
    $position = $_POST["position"] ?? null;
    $govId = $_POST["govId"] ?? null;
    $accreditation = $_POST["accreditation"] ?? null;
    $role = $_POST["role"] ?? null;

    // Generate bcrypt hash (compatible with Node.js bcrypt.hashSync)
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    if (empty($email) || empty($password) || empty($name)) {
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
                INSERT INTO users (account_type, email, password_hash, full_name, role, contact_number, status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->bind_param("ssssss", $user_type, $email, $hashedPassword, $name, $role, $contact);

            if ($stmt->execute()) {
                $userId = $conn->insert_id;

                // Insert into role-specific table
                if ($user_type === "agency") {
                    $agencyStmt = $conn->prepare("
                        INSERT INTO agencies (user_id, agency_name, office_code, position, gov_id_number)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $agencyStmt->bind_param("issss", $userId, $name, $officeCode, $position, $govId);
                    $agencyStmt->execute();
                    $msg = "✅ Agency registered successfully.";
                } elseif ($user_type === "auditor") {
                    $auditorStmt = $conn->prepare("
                        INSERT INTO auditors (user_id, organization_name, office_code, accreditation_number)
                        VALUES (?, ?, ?)
                    ");
                    $auditorStmt->bind_param("iss", $userId, $name, $officeCode, $accreditation);
                    $auditorStmt->execute();
                    $msg = "✅ Auditor registered successfully.";
                } elseif ($user_type === "citizen") {
                    $msg = "✅ Citizen registered successfully (auto-approved).";
                } else {
                    $msg = "✅ User registered.";
                }
                header("Location: ./login.php");
            } else {
                $msg = "❌ Error registering user: " . $stmt->error;
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
  <link href="../../../frontend/dist/output.css" rel="stylesheet">
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
        <input type="text" name="position" placeholder="Position/Role" class="w-full p-2 border rounded mb-2" required>
        <input type="text" name="govId" placeholder="Government ID Number" class="w-full p-2 border rounded mb-2" required>
        <input type="text" name="contact" placeholder="Contact Number" class="w-full p-2 border rounded mb-2" required>
        <input type="password" name="password" placeholder="Password" class="w-full p-2 border rounded mb-2" required>
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
        <input type="password" name="password" placeholder="Password" class="w-full p-2 border rounded mb-2" required>
        <button class="w-full bg-green-600 text-white p-2 rounded hover:bg-green-700">Register</button>
      </form>

      <!-- Citizen Form -->
      <form id="citizen-form" class="tab-content hidden" method="POST">
        <input type="hidden" name="user_type" value="citizen">
        <input type="text" name="name" placeholder="Full Name" class="w-full p-2 border rounded mb-2" required>
        <input type="email" name="email" placeholder="Email Address" class="w-full p-2 border rounded mb-2" required>
        <input type="text" name="contact" placeholder="Contact Number" class="w-full p-2 border rounded mb-2" required>
        <input type="password" name="password" placeholder="Password" class="w-full p-2 border rounded mb-2" required>
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
