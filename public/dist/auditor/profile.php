<?php
  session_start();
  include("../../../config/config.php");
  include("../../../DAO/UserDao.php");
  include("../../../utils/session/checkSession.php");
  include("./controller/checkAccess.php");
  include("./controller/profileController.php");
  
  //$isSaved = $_SESSION['isSaved'] ?? '';
  $editMode = $_SESSION['isEdit'];
  //$error = $_SESSION['error'] ?? '';
  //$changed = $_SESSION['changed'] ??'';

  $dao = new UserDAO($conn);
  $user = $dao->getUserById($_SESSION['user']['id']);
  $auditor = $dao->getUserByIdFromAuditor($_SESSION['user']['id']);
?>
<!doctype html>
<html lang="en" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" dir="ltr" data-pc-theme="light">
<head>
  <title>Profile</title>
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
    <link rel="stylesheet" href="../../src/output.css">
    <link
      rel="stylesheet"
      type="text/css"
      href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"
    />
    <link
      rel="stylesheet"
      type="text/css"
      href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"
    />
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
   <?php include '../includes/auditor-sidebar.php'; ?>
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
            <h5 class="mb-0 font-medium">Profile</h5>
          </div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="./dashboard.php">Home</a></li>
            <li class="breadcrumb-item" aria-current="page">Profile</li>
          </ul>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->

      <!-- [ Main Content ] start -->
      <div class="card grid grid-cols-12 gap-x-6">
        <!-- [ sample-page ] start -->
        <div class="col-span-12 md:col-span-6">
          <div>
            <div class="card-header">
              <h5>Account Details</h5>
            </div>
            <div class="card-body">
              <?php if ($editMode): ?>
                      <p style="text-align:center; color:green; font-weight:bold;">
                        Editing...
                      </p>
              <?php endif; ?>
              <form class="form-horizontal" method="POST"> <!-- Form elements -->
                <?php if ($editMode): ?>
                     <div class="mb-3">
                      <label for="floatingInput" class="form-label">Username:</label>
                      <input type="text" name="name" class="form-control" id="floatingInput" placeholder="Input 1" value="<?= htmlspecialchars($user['full_name'])?>"/>
                    </div>
                    <div class="mb-4">
                      <label for="floatingInput1"  class="form-label">Email:</label>
                      <input type="text" name="email" class="form-control" id="floatingInput1" placeholder="Input 2" value="<?= htmlspecialchars($user['email'])?>"/>
                    </div>
                    <div class="mb-4">
                      <label for="floatingInput1" class="form-label">Contact:</label>
                      <input type="text" name="contact" class="form-control" id="floatingInput1" placeholder="Input 2" value="<?= htmlspecialchars($user['contact_number'])?>"/>
                    </div>
                <?php else: ?>
                  <div class="mb-3">
                    <label for="floatingInput" name="name" class="form-label">Username:</label>
                    <input type="text" class="form-control" id="floatingInput" placeholder="Input 1" value="<?= htmlspecialchars($user['full_name'])?>" readonly/>
                  </div>
                  <div class="mb-4">
                    <label for="floatingInput1" name="email" class="form-label">Email:</label>
                    <input type="text" class="form-control" id="floatingInput1" placeholder="Input 2" value="<?= htmlspecialchars($user['email'])?>" readonly/>
                  </div>
                  <div class="mb-4">
                    <label for="floatingInput1" name="contact" class="form-label">Contact:</label>
                    <input type="text" class="form-control" id="floatingInput1" placeholder="Input 2" value="<?= htmlspecialchars($user['contact_number'])?>" readonly/>
                  </div>
                  
                <?php endif; ?>
                <div class="mb-4">
                  <label for="floatingInput1" class="form-label">Account Type:</label>
                  <input type="text" class="form-control" id="floatingInput1" placeholder="Input 2" value="<?= htmlspecialchars($user['account_type'])?>" readonly/>
                </div>
                <div class="mb-4">
                  <label for="floatingInput1" class="form-label">Wallet:</label>
                  <input type="text" class="form-control" id="floatingInput1" placeholder="Input 2" value="<?= htmlspecialchars($auditor['wallet_address'])?>" readonly/>
                </div>
                <div class="flex mt-1 justify-between items-center flex-wrap">
                  <div class="form-check">
                    <button type="submit" name="action" value="edit" class="btn mx-auto shadow-2xl"><i data-feather="edit"></i></button>
                    <button type="submit" name="action" value="save" class="btn text-white bg-success-700 mx-auto shadow-2xl">Save</button>
                  </div>
                </div>
              </form> <!-- Form ends -->
            </div>
          </div>
        </div>
        <div class="col-span-12 md:col-span-6">
          <div>
            <div class="card-header">
              <h5>Change Password</h5>
            </div>
            <div class="card-body">
              
              <form class="form-horizontal" method="POST"> <!-- Form elements -->
                <div class="mb-4">
                  <label for="floatingInput" class="form-label">Old Password</label>
                  <input type="password" required name="oldPassword" class="form-control" id="floatingInput" placeholder="Enter your old password" />
                </div>
                <div class="mb-3">
                  <label for="floatingInput1" class="form-label">New Password</label>
                  <input type="password" required name="newPassword" class="form-control" id="floatingInput1" placeholder="Enter your new password" />
                </div>
                <div class="mb-3">
                  <label for="floatingInput1" class="form-label">Confirm Password</label>
                  <input type="password" required name="confirm" class="form-control" id="floatingInput1" placeholder="Confirm your new password" />
                </div>
                <div class="flex mt-1 justify-between items-center flex-wrap">
                  <div class="form-check">
                    <button type="submit" name="action" value="change"class = "btn text-white bg-success-700 mx-auto shadow-2xl">Change password</button>
                    <a href="../auth/forgotPassword.php" class="text-blue-500">forgot password?</a>
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
