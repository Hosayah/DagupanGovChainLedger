<?php
  session_start();
  include("../../../config/config.php");
  include("../../../DAO/UserDao.php");
  include("../govagency/controller/checkAccess.php");
  include("../../../utils/session/checkSession.php");
  include("../govagency/controller/checkAccess.php");
  include("../govagency/controller/profileController.php");

  if (!isset($_SESSION['isEdit'])) {
      $_SESSION['isEdit'] = false;
  }

  $editMode = $_SESSION['isEdit'];;

  $user = $_SESSION["user"];
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
   <?php include '../includes/govagency-sidebar.php'; ?>
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
            <li class="breadcrumb-item"><a href="../admin/dashboard.php">Home</a></li>
            <li class="breadcrumb-item" aria-current="page">Agency Profile</li>
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
                      <input type="text" class="form-control" id="floatingInput" placeholder="Input 1" value="<?= htmlspecialchars($user['name'])?>"/>
                    </div>
                <?php else: ?>
                  <div class="mb-3">
                    <label for="floatingInput" class="form-label">Username:</label>
                    <input type="text" class="form-control" id="floatingInput" placeholder="Input 1" value="<?= htmlspecialchars($user['name'])?>" readonly/>
                  </div>
                  
                <?php endif; ?>
                <div class="mb-4">
                    <label for="floatingInput1" class="form-label">Wallet ID: (Immutable)</label>
                    <input type="text" class="form-control" id="floatingInput1" placeholder="Input 2" value="<?= htmlspecialchars($user['wallet_address'])?>" readonly/>
                  </div>
                <div class="flex mt-1 justify-between items-center flex-wrap">
                  <div class="form-check">
                    <button type="submit" name="action" value="edit" class="btn mx-auto shadow-2xl"><i data-feather="edit"></i></button>
                    <button type="submit" name="action" value="save" class="btn btn-primary mx-auto shadow-2xl">Save</button>
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
