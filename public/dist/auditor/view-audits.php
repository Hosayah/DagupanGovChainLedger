<?php
  session_start();
  include("../../../config/config.php");
  include("../../../DAO/AuditDao.php");
  include("../auditor/controller/checkAccess.php");
  include("../../../utils/session/checkSession.php");
  include("../auditor/controller/checkAccess.php");
  include("../auditor/controller/tablePageController.php");
  
  if (!isset($_SESSION['limit'])) {
      $_SESSION['limit'] = 0;
  }

  $limit = $_SESSION['limit'];
  $user_id = $_SESSION["user"]["id"];
  $auditDao = new AuditDAO($conn);
  $search_term = $_GET['search_term'] ?? '';
  $auditList = $auditDao->getAuditByUserIdWithSearch($user_id,$limit, $search_term);
?>
<!doctype html>
<html lang="en" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" dir="ltr" data-pc-theme="light">
<head>
  <title>View Audits</title>
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
            <h5 class="mb-0 font-medium">View-Audits</h5>
          </div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="../admin/dashboard.php">Home</a></li>
            <li class="breadcrumb-item" aria-current="page">View-audits</li>
          </ul>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->

      <!-- [ Main Content ] start -->
       <div class="grid grid-cols-12 gap-x-6">
        <!-- [ sample-page ] start -->
        <div class="col-span-12">
          <div class="card">
            <div class="card-header flex justify-between">
              <h5 class="mt-4">Audits List</h5>
              <form method="GET" id="filterForm">
                  <input type="text" name="search_term" id="searchInput" placeholder="Search by title, category, or description"
                    value="<?php echo htmlspecialchars($search_term); ?>" class="border-2 shadow-2xl w-150 p-2" />
                  <button type="submit" class="btn btn-transparent"><a href="#"><i data-feather="search"></i></a></button>
              </form>
            </div>
            <div class="card-body">
              <form class="form-horizontal" method="POST"> <!-- Form elements -->
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
                      <th class="font-weight-bold">Audit ID</th>
                      <th class="font-weight-bold">Title</th>
                      <th class="font-weight-bold">Record ID</th>
                      <th class="font-weight-bold">Result</th>
                      <th class="font-weight-bold">Submitted By</th>
                      <th class="font-weight-bold">Submitted at</th>
                      <th class="font-weight-bold">Action</th>
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
                            <h6 class="mb-0">USER-ID<?= htmlspecialchars($row['audit_by']) ?></h6>
                          </td>
                          <td>
                            <h6 class="text-muted">
                              <i class="fas fa-circle text-warning-500 text-[10px] ltr:mr-4 rtl:ml-4"></i>
                              <?= htmlspecialchars($row['audited_at']) ?>
                            </h6>
                          </td>
                          <td>
                            <a href="./edit-audit.php?id=<?= $row['audit_id'] ?>&action=edit" 
                              class="badge bg-theme-bg-2 text-white text-[12px] mx-2">
                              Edit & View
                            </a>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    <?php else: ?>
                      <tr><td colspan="8" style="text-align:center;">No Audits found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                  </table>
                </div>
                <div class="flex mt-1 justify-between items-center flex-wrap">
                  <button type="submit" class="btn mx-auto shadow-2xl" name="next" value="dec"><i data-feather="arrow-left"></i></button>
                  <?= htmlspecialchars($limit)?>
                  <button type="submit" class="btn mx-auto shadow-2xl" name="next" value="inc"><i data-feather="arrow-right"></i></button>
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
</html
