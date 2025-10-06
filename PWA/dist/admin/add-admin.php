<?php
session_start();
include("../../../config/config.php"); // DB connection file

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_type = "admin";
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm = trim($_POST["confirm"]);
    $contact = trim($_POST["contact"]);

    // Generate bcrypt hash (compatible with Node.js bcrypt.hashSync)
    if($password != $confirm) {
      $msg = "Password don't match";
    } else {
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
                  INSERT INTO users (account_type, email, password_hash, full_name, contact_number, status)
                  VALUES (?, ?, ?, ?, ?, 'approved')
              ");
              $stmt->bind_param("sssss", $user_type, $email, $hashedPassword, $name, $contact);

              if ($stmt->execute()) {
                  $userId = $conn->insert_id;

                  $adminStmt = $conn->prepare("
                      INSERT INTO admins (user_id, access_level)
                      VALUES (?, 'review_admin')
                  ");
                  $adminStmt->bind_param("i", $userId);
                  $adminStmt->execute();
                  $msg = "✅ Admin Created successfully.";
                  
              } else {
                  $msg = "❌ Error registering user: " . $stmt->error;
              }
          }
      }
    }
}
?>
<!doctype html>
<html lang="en" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" dir="ltr" data-pc-theme="light">
<head>
  <title>Template02</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="." />
    <meta name="keywords" content="." />
    <meta name="author" content="Sniper 2025" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/fonts/phosphor/duotone/style.css" />
    <link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" />
    <link rel="stylesheet" href="../assets/fonts/feather.css" />
    <link rel="stylesheet" href="../assets/fonts/fontawesome.css" />
    <link rel="stylesheet" href="../assets/fonts/material.css" />
    <link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" />
</head>
<body>
  <!-- [ Pre-loader ] start -->
<div class="loader-bg fixed inset-0 bg-white dark:bg-themedark-cardbg z-[1034]">
  <div class="loader-track h-[5px] w-full inline-block absolute overflow-hidden top-0">
    <div class="loader-fill w-[300px] h-[5px] bg-primary-500 absolute top-0 left-0 animate-[hitZak_0.6s_ease-in-out_infinite_alternate]"></div>
  </div>
</div>
<!-- [ Pre-loader ] End -->
<!-- [ Sidebar Menu ] start -->
  <?php include '../includes/admin-sidebar.php'; ?>
<!-- [ Sidebar Menu ] end -->
<!-- [ Header Topbar ] start -->
  <?php include '../includes/header.php'; ?>
<!-- [ Header ] end -->

  <!-- [ Main Content ] start -->
  <div class="pc-container">
    <div class="pc-content">
      <!-- [ breadcrumb ] start -->
      <div class="page-header">
        <div class="page-block">
          <div class="page-header-title">
            <h5 class="mb-0 font-medium">Add Admin</h5>
          </div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="../admin/dashboard.php">Home</a></li>
            <li class="breadcrumb-item" aria-current="page">add-admin</li>
          </ul>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->

      <!-- [ Main Content ] start -->
      <div class="grid grid-cols-12 gap-x-6">
        <!-- [ sample-page ] start -->
        <div class="col-span-12">
          <div class="card">
            <div class="card-header">
              <h5>Add Admin</h5>
            </div>
            <div class="card-body">
              <form class="form-horizontal" method="POST"> <!-- Form elements -->
                <div class="mb-3">
                  <label for="floatingInput" class="form-label">Name:</label>
                  <input type="text" class="form-control" name="name" id="floatingInput" placeholder="E.g Juan Dela Cruz" />
                </div>
                <div class="mb-4">
                  <label for="floatingInput1" class="form-label">Email:</label>
                  <input type="email" class="form-control" name="email" id="floatingInput1" placeholder="adminDagupan@gov.ph" required/>
                </div>
                <div class="mb-4">
                  <label for="floatingInput1" class="form-label">Contact Number:</label>
                  <input type="number" class="form-control" name="contact" id="floatingInput1" placeholder="0912387612" required/>
                </div>
                <div class="mb-4">
                  <label for="floatingInput1" class="form-label">Password:</label>
                  <input type="password" class="form-control" name="password" id="floatingInput1" placeholder="Enter a password" required/>
                </div>
                <div class="mb-4">
                  <label for="floatingInput1" class="form-label">Confirm Password:</label>
                  <input type="password" class="form-control" name="confirm" id="floatingInput1" placeholder="Enter a password" required/>
                </div>
                <div class="flex mt-1 justify-between items-center flex-wrap">
                  <div class="form-check">
                    <button type="submit" class="btn btn-primary mx-auto shadow-2xl">Create Admin</button>
                    <?php if (!empty($msg)): ?>
                      <p id="msg" class="text-sm mb-3 <?= strpos($msg, '✅') !== false ? 'text-green-600' : 'text-red-600' ?>">
                        <?= $msg ?>
                      </p>
                    <?php endif; ?>
                  </div>
                </div>
              </form> <!-- Form ends -->
            </div>
          </div>
        </div>
        <!-- [ sample-page ] end -->
      </div>
      <!-- [ Main Content ] end -->
    </div>
  </div>
  <!-- [ Main Content ] end -->

<!-- Required Js -->
<script src="../assets/js/plugins/simplebar.min.js"></script>
<script src="../assets/js/plugins/popper.min.js"></script>
<script src="../assets/js/icon/custom-icon.js"></script>
<script src="../assets/js/plugins/feather.min.js"></script>
<script src="../assets/js/component.js"></script>
<script src="../assets/js/theme.js"></script>
<script src="../assets/js/script.js"></script>

<?php include '../includes/footer.php'; ?>
</body>
</html>
