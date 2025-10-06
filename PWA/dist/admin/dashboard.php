<?php
  session_start();
  include("../../../config/config.php");
  if (!isset($_SESSION['user'])) {
    header("Location: ../../auth/login.php");
    exit;
  } else { 
    if (!isset($_SESSION['user']) || $_SESSION['user']['account_type'] !== 'admin') {
    http_response_code(403);
    die("Access denied. Admins only.");
  }

  // Check access level
  $accessLevel = $_SESSION['user']['access_level'] ?? null;
  if (!in_array($accessLevel, ['super_admin', 'review_admin'])) {
      http_response_code(403);
      die("You do not have permission to view this page.");
  }
  include("../../../config/get_account_request.php");
  include("../../../config/get_user_count.php");
  
  $approved_percentage = round((($approved / $totalUsers) * 100), 2);
  $pending_percentage = round((($pending / $totalUsers) * 100), 2);

  $agency_percentage = round((($agency / $totalUsers) * 100), 2);
  $auditor_percentage = round((($auditor / $totalUsers) * 100), 2);
  $citizen_percentage = round((($citizen / $totalUsers) * 100), 2);
?>
  <!doctype html>
  <html lang="en" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" dir="ltr" data-pc-theme="light">

  <head>
    <title>Dashboard</title>
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
              <h5 class="mb-0 font-medium">Dashboard</h5>
            </div>
            <ul class="breadcrumb">
              <li class="breadcrumb-item"><a href="../admin/dashboard.php">Home</a></li>
              <!-- <li class="breadcrumb-item"><a href="javascript: void(0)">Dashboard</a></li> -->
              <li class="breadcrumb-item" aria-current="page">Dashboard</li>
            </ul>
          </div>
        </div>
        <!-- [ breadcrumb ] end -->

        <!-- [ Main Content ] start -->
        <div class="grid grid-cols-12 gap-x-6">
          <div class="col-span-12 xl:col-span-4 md:col-span-6">
            <div class="card">
              <div class="card-header !pb-0 !border-b-0">
                <h5>Agency Accounts</h5>
              </div>
              <div class="card-body">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                  <h3 class="font-light flex items-center mb-0">
                    <i class="feather icon-arrow-up text-success-500 text-[30px] mr-1.5"></i>
                    <?= htmlspecialchars($agency) ?>
                  </h3>
                  <p class="mb-0"><?= htmlspecialchars($agency_percentage) ?>%</p>
                </div>
                <div class="w-full bg-theme-bodybg rounded-lg h-1.5 mt-6 dark:bg-themedark-bodybg">
                  <div class="bg-theme-bg-1 h-full rounded-lg shadow-[0_10px_20px_0_rgba(0,0,0,0.3)]" role="progressbar"
                    style="width: <?= htmlspecialchars($agency_percentage) ?>%"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 xl:col-span-4 md:col-span-6">
            <div class="card">
              <div class="card-header !pb-0 !border-b-0">
                <h5>Auditor Accounts</h5>
              </div>
              <div class="card-body">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                  <h3 class="font-light flex items-center mb-0">
                    <i class="feather icon-arrow-down text-danger-500 text-[30px] mr-1.5"></i>
                    <?= htmlspecialchars($auditor) ?>
                  </h3>
                  <p class="mb-0"><?= htmlspecialchars($auditor_percentage) ?>%</p>
                </div>
                <div class="w-full bg-theme-bodybg rounded-lg h-1.5 mt-6 dark:bg-themedark-bodybg">
                  <div class="bg-theme-bg-2 h-full rounded-lg shadow-[0_10px_20px_0_rgba(0,0,0,0.3)]" role="progressbar"
                    style="width: <?= htmlspecialchars($auditor_percentage) ?>%"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 xl:col-span-4 md:col-span-6">
            <div class="card">
              <div class="card-header !pb-0 !border-b-0">
                <h5>Citizen Accounts</h5>
              </div>
              <div class="card-body">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                  <h3 class="font-light flex items-center mb-0">
                    <i class="feather icon-arrow-down text-danger-500 text-[30px] mr-1.5"></i>
                    <?= htmlspecialchars($citizen) ?>
                  </h3>
                  <p class="mb-0"><?= htmlspecialchars($citizen_percentage) ?>%</p>
                </div>
                <div class="w-full bg-theme-bodybg rounded-lg h-1.5 mt-6 dark:bg-themedark-bodybg">
                  <div class="bg-theme-bg-2 h-full rounded-lg shadow-[0_10px_20px_0_rgba(0,0,0,0.3)]" role="progressbar"
                    style="width: <?= htmlspecialchars($citizen_percentage) ?>%"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 md:col-span-6">
            <div class="card">
              <div class="card-header !pb-0 !border-b-0">
                <h5>Total Approved Accounts</h5>
              </div>
              <div class="card-body">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                  <h3 class="font-light flex items-center mb-0">
                    <i class="fas fa-circle text-success text-[12px] mr-1.5"></i>
                    <?= htmlspecialchars($approved) ?>
                  </h3>
                  <p class="mb-0"><?= htmlspecialchars($approved_percentage) ?>%</p>
                </div>
                <div class="w-full bg-theme-bodybg rounded-lg h-1.5 mt-6 dark:bg-themedark-bodybg">
                  <div class="bg-theme-bg-2 h-full rounded-lg shadow-[0_10px_20px_0_rgba(0,0,0,0.3)]" role="progressbar"
                    style="width: <?= htmlspecialchars($approved_percentage) ?>%"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 md:col-span-6">
            <div class="card">
              <div class="card-header !pb-0 !border-b-0">
                <h5>Pending Accounts</h5>
              </div>
              <div class="card-body">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                  <h3 class="font-light flex items-center mb-0">
                    <i class="fas fa-circle text-warning-500 text-[12px] mr-1.5"></i>
                    <?= htmlspecialchars($pending) ?>
                  </h3>
                  <p class="mb-0"><?= htmlspecialchars($pending_percentage) ?>%</p>
                </div>
                <div class="w-full bg-theme-bodybg rounded-lg h-1.5 mt-6 dark:bg-themedark-bodybg">
                  <div class="bg-theme-bg-2 h-full rounded-lg shadow-[0_10px_20px_0_rgba(0,0,0,0.3)]" role="progressbar"
                    style="width: <?= htmlspecialchars($pending_percentage) ?>%"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12">
            <div class="card table-card">
              <div class="card-header">
                <h5>Account Requests</h5>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <?php if (isset($_SESSION['flash'])): ?>
                    <p style="text-align:center; color:green; font-weight:bold;">
                      <?= htmlspecialchars($_SESSION['flash']) ?>
                    </p>
                      <?php unset($_SESSION['flash']); ?>
                  <?php endif; ?>
                  <table class="table table-hover">
                    <thead>
                    <tr class="bg-dark text-white text-center font-weight-bold">
                      <th class="font-weight-bold">No</th>
                      <th class="font-weight-bold">Account Type</th>
                      <th class="font-weight-bold">Office/Agency Details</th>
                      <th class="font-weight-bold">User Details</th>
                      <th class="font-weight-bold">Entry Date</th>
                      <th class="font-weight-bold">Action</th>
                    </tr>
                  </thead>
                    <tbody>
                      <?php if ($result->num_rows > 0): ?>
                      <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                          <td>
                            <h6 class="mb-0"><?= htmlspecialchars($row['user_id']) ?></h6>
                          </td>
                          <td>
                            <h6 class="mb-1"><?= htmlspecialchars($row['account_type']) ?></h6>
                          </td>
                          <td>
                            <h6 class="mb-0">Organization:</h6>
                            <p class="mb-1"><?= htmlspecialchars($row['organization'] ?? '-') ?></p>
                            <h6 class="mb-0">Office code:</h6>
                             <p class="mb-1"><?= htmlspecialchars($row['officeCode'] ?? '-') ?></p>
                          </td>
                          <td>
                            <h6 class="mb-0">Name: </h6>
                            <p class="mb-1"><?= htmlspecialchars($row['full_name']) ?> </p>
                            <h6 class="mb-0">Role:</h6>
                            <p class="mb-1"><?= htmlspecialchars($row['role'] ?? '-') ?></p>
                            <h6 class="mb-0">Government ID No.:</h6>
                            <p class="mb-1"><?= htmlspecialchars($row['identifier'] ?? '-') ?></p>
                          </td>
                          <td>
                            <h6 class="text-muted">
                              <i class="fas fa-circle text-warning-500 text-[10px] ltr:mr-4 rtl:ml-4"></i>
                              <?= htmlspecialchars($row['created_at']) ?>
                            </h6>
                          </td>
                          <td>
                            <a href="./controller/update-status?id=<?= $row['user_id'] ?>&action=reject" 
                              class="badge bg-theme-bg-2 text-white text-[12px] mx-2"
                              onclick="return confirm('Are you sure you want to reject this user?')">
                              Reject
                            </a>

                            <a href="./controller/update-status.php?id=<?= $row['user_id'] ?>&action=approve" 
                              class="badge bg-theme-bg-1 text-white text-[12px]"
                              onclick="return confirm('Approve this user account?')">
                              Approve
                            </a>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    <?php else: ?>
                      <tr><td colspan="8" style="text-align:center;">No pending users found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- [ Main Content ] end -->
      </div>
    </div>
    <!-- [ Main Content ] end -->
    <?php include '../includes/footer.php'; ?>

    <!-- Required Js -->
    <script src="../assets/js/plugins/simplebar.min.js"></script>
    <script src="../assets/js/plugins/popper.min.js"></script>
    <script src="../assets/js/icon/custom-icon.js"></script>
    <script src="../assets/js/plugins/feather.min.js"></script>
    <script src="../assets/js/component.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/script.js"></script>
    <div class="floting-button fixed bottom-[50px] right-[30px] z-[1030]">
    </div>

    <script>
      layout_change('false');
      layout_theme_sidebar_change('dark');
      change_box_container('false');
      layout_caption_change('true');
      layout_rtl_change('false');
      preset_change('preset-1');
      main_layout_change('vertical');
    </script>
  </body>
  <!-- [Body] end -->

  </html>
<?php } ?>