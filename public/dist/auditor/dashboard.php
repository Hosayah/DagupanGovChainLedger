<?php
  session_start();
  include("../../../config/config.php");
  include("../../../DAO/ProjectDao.php");
  include("../../../DAO/RecordDao.php");
  include("../../../DAO/AuditDao.php");
  include("../auditor/controller/checkAccess.php");
  include("../../../utils/session/checkSession.php");
  
  
  $user_id = $_SESSION["user"]["id"];
  $projectDao = new ProjectDAO($conn);
  $auditDao = new AuditDAO($conn);

  $auditList = $auditDao->getAuditByUserId($user_id);

  $pcounters = $projectDao->getProjectCounters($user_id);
  $auditCounter = $auditDao->getAuditCounters($user_id);
  
  $totalAudits = $auditCounter['total'] > 0 ? $auditCounter['total'] : 1;
  $totalPassed = $auditCounter['passed'] > 0 ? $auditCounter['passed'] : 1;
  $totalFlagged = $auditCounter['flagged'] > 0 ? $auditCounter['flagged'] : 1;
  $totalRejected = $auditCounter['rejected'] > 0 ? $auditCounter['rejected'] : 1;
  
  $auditsPercentage   = round(($auditCounter['auditorTotal']   / $totalAudits) * 100, 2);
  $passedPercentage   = round(($auditCounter['auditorPassed']   / $totalPassed) * 100, 2);
  $flaggedPercentage   = round(($auditCounter['auditorFlagged']   / $totalFlagged) * 100, 2);
  $rejectedPercentage   = round(($auditCounter['auditorRejected']   / $totalRejected) * 100, 2);
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
                <h5>Total Projects</h5>
              </div>
              <div class="card-body">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                  <h3 class="font-light flex items-center mb-0">
                    <img src="https://img.icons8.com/ios/24/12B886/project.png" alt="project"/>&nbsp;
                    <?= htmlspecialchars($pcounters['total']) ?>
                  </h3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 xl:col-span-4 md:col-span-6">
            <div class="card">
              <div class="card-header !pb-0 !border-b-0">
                <h5>Total Audits</h5>
              </div>
              <div class="card-body">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                  <h3 class="font-light flex items-center mb-0">
                    <img src="https://img.icons8.com/dotty/32/12B886/fine-print.png" alt="fine-print"/>&nbsp;
                    <?= htmlspecialchars($totalAudits) ?>
                  </h3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 xl:col-span-4 md:col-span-6">
            <div class="card">
              <div class="card-header !pb-0 !border-b-0">
                <h5>Audits Submitted</h5>
              </div>
              <div class="card-body">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                  <h3 class="font-light flex items-center mb-0">
                    <img src="https://img.icons8.com/dotty/32/12B886/fine-print.png" alt="fine-print"/> &nbsp;
                    <?= htmlspecialchars($auditCounter['auditorTotal']) ?>
                  </h3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 xl:col-span-4 md:col-span-6">
            <div class="card">
              <div class="card-header !pb-0 !border-b-0">
                <h5>Passed Audits</h5>
              </div>
              <div class="card-body">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                  <h3 class="font-light flex items-center mb-0">
                    <?= htmlspecialchars($auditCounter['auditorPassed']) ?>
                  </h3>
                  <p class="mb-0"><?= htmlspecialchars($passedPercentage) ?>%</p>
                </div>
                <div class="w-full bg-theme-bodybg rounded-lg h-1.5 mt-6 dark:bg-themedark-bodybg">
                  <div class="bg-theme-bg-1 h-full rounded-lg shadow-[0_10px_20px_0_rgba(0,0,0,0.3)]" role="progressbar"
                    style="width: <?= htmlspecialchars($passedPercentage) ?>%"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 xl:col-span-4 md:col-span-6">
            <div class="card">
              <div class="card-header !pb-0 !border-b-0">
                <h5>Flagged Audits</h5>
              </div>
              <div class="card-body">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                  <h3 class="font-light flex items-center mb-0">
                    <?= htmlspecialchars($auditCounter['auditorFlagged']) ?>
                  </h3>
                  <p class="mb-0"><?= htmlspecialchars($flaggedPercentage) ?>%</p>
                </div>
                <div class="w-full bg-theme-bodybg rounded-lg h-1.5 mt-6 dark:bg-themedark-bodybg">
                  <div class="bg-theme-bg-1 h-full rounded-lg shadow-[0_10px_20px_0_rgba(0,0,0,0.3)]" role="progressbar"
                    style="width: <?= htmlspecialchars($flaggedPercentage) ?>%"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 xl:col-span-4 md:col-span-6">
            <div class="card">
              <div class="card-header !pb-0 !border-b-0">
                <h5>Rejected Audits</h5>
              </div>
              <div class="card-body">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                  <h3 class="font-light flex items-center mb-0">
                    <?= htmlspecialchars($auditCounter['auditorRejected']) ?>
                  </h3>
                  <p class="mb-0"><?= htmlspecialchars($rejectedPercentage) ?>%</p>
                </div>
                <div class="w-full bg-theme-bodybg rounded-lg h-1.5 mt-6 dark:bg-themedark-bodybg">
                  <div class="bg-theme-bg-1 h-full rounded-lg shadow-[0_10px_20px_0_rgba(0,0,0,0.3)]" role="progressbar"
                    style="width: <?= htmlspecialchars($rejectedPercentage) ?>%"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12">
            <div class="card table-card">
              <div class="card-header">
                <h5>Recent Audits</h5>
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
                      <tr class="bg-dark text-white text-center font-weight-bold">
                      <th class="font-weight-bold">Audit ID</th>
                      <th class="font-weight-bold">Title</th>
                      <th class="font-weight-bold">Record ID</th>
                      <th class="font-weight-bold">Result</th>
                      <th class="font-weight-bold">Submitted By</th>
                      <th class="font-weight-bold">Submitted at</th>
                      <th class="font-weight-bold">Action</th>
                    </tr>
                    </tr>
                  </thead>
                    <tbody>
                      <?php if ($auditList->num_rows > 0): ?>
                      <?php while ($row = $auditList->fetch_assoc()): ?>
                        <tr>
                          <td>
                            <h6 class="mb-0">AU-ID-<?= htmlspecialchars($row['audit_id']) ?></h6>
                          </td>
                          <td>
                            <h6 class="mb-1"><?= htmlspecialchars($row['title']) ?></h6>
                          </td>
                          <td>
                            <h6 class="mb-0">R-ID-<?= htmlspecialchars($row['record_id']) ?></h6>
                          </td>
                           <td>
                            <h6 class="mb-1"><?= htmlspecialchars($row['result']) ?></h6>
                          </td>
                          <td>
                            <h6 class="mb-0"><?= htmlspecialchars($row['audit_by']) ?></h6>
                          </td>
                          <td>
                            <h6 class="text-muted">
                              <i class="fas fa-circle text-warning-500 text-[10px] ltr:mr-4 rtl:ml-4"></i>
                              <?= htmlspecialchars($row['audited_at']) ?>
                            </h6>
                          </td>
                          <td>
                            <a href="./edit-audit.php?id=<?= $row['audit_id'] ?>&action=edit" 
                              class="badge bg-theme-bg-1 text-white text-[12px]">
                              Edit & View
                            </a>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    <?php else: ?>
                      <tr><td colspan="8" style="text-align:center;">No Auits found.</td></tr>
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
