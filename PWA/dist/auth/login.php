<?php
session_start();
include("../../../config/config.php"); // your DB connection

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $hash = password_hash($password, PASSWORD_BCRYPT);

    echo "Plain password: $password<br>";
    echo "Generated hash: $hash<br>";

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
                        'account_type' => $user['account_type'],
                        'name' => $user['full_name'],
                        'role' => $user['role'],
                        'status' => $user['status']
                    ];

                    // Redirect based on account type
                    if ($user['account_type'] === 'agency') {
                        header("Location: ../../GovAgency/dashboard.html");
                        exit;
                    } elseif ($user['account_type'] === 'auditor') {
                        header("Location: ../../Auditor/dashboard.html");
                        exit;
                    } elseif ($user['account_type'] === 'admin') {
                        header("Location: ../../Admin/dashboard.html");
                        exit;
                    } else {
                        header("Location: ../admin/dashboard.php");
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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link href="../../../frontend/dist/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
  <div class="max-w-md mx-auto bg-white shadow-lg rounded-xl p-6 mt-20">
    <h2 class="text-2xl font-bold mb-4">Login</h2>
    <?php if (!empty($msg)): ?>
      <p id="msg" class="text-sm mb-3 <?= strpos($msg, '✅') !== false ? 'text-green-600' : 'text-red-600' ?>">
        <?= $msg ?>
      </p>
    <?php endif; ?>

    <form id="loginForm" method="POST" class="space-y-4">
      <input type="email" name="email" placeholder="Email" required class="w-full p-3 border rounded-lg focus:outline-none focus:ring">
      <input type="password" name="password" placeholder="Password" required class="w-full p-3 border rounded-lg focus:outline-none focus:ring">
      <button type="submit" class="w-full bg-green-600 text-white p-3 rounded-lg hover:bg-green-700">
        Login
      </button>
    </form>

    <p class="mt-4 text-sm text-gray-600">
      Don’t have an account?
      <a href="./registration.php" class="text-green-600 hover:underline">Register here</a>
    </p>
  </div>
</body>
</html>
