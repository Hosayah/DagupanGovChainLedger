<?php
  session_start();
  include("../../../config/config.php");
  include("../../../DAO/ProjectDao.php");
  //include("../govagency/controller/checkAccess.php");
  include("../../../utils/session/checkSession.php");
  include("../govagency/controller/tablePageController.php");
  
  if (!isset($_SESSION['limit'])) {
      $_SESSION['limit'] = 0;
  }

  $limit = $_SESSION['limit'];
  //$user_id = $_SESSION["user"]["id"];
  $projectDao = new ProjectDAO($conn);
  $projectsList = $projectDao->getAllProjects($limit);
?>
<!doctype html>
<html lang="en" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" dir="ltr" data-pc-theme="light">
<head>
  <title>View Projects</title>
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
  <?php include '../includes/citizen-sidebar.php'; ?>
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
            <h5 class="mb-0 font-medium">View-Projects</h5>
          </div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="../admin/dashboard.php">Home</a></li>
            <li class="breadcrumb-item" aria-current="page">View-projects</li>
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
              <h5>View Projects</h5>
            </div>
            <div class="card-body">
              <form class="form-horizontal" method="POST"> <!-- Form elements -->
                <div class="mb-3 flex">
                  <form action="" method="GET">
                    <input type="search" class="form-control !shadow-none" id="floatingInput" placeholder="Search by name"/>
                    <button type="button" class="btn btn-transparent mx-auto shadow-2xl"><a href="#"><i data-feather="search"></i></a></button>
                  </form>
                </div>
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
                      <th class="font-weight-bold">Project ID</th>
                      <th class="font-weight-bold">Title</th>
                      <th class="font-weight-bold">Category</th>
                      <th class="font-weight-bold">Description</th>
                      <th class="font-weight-bold">Created By</th>
                      <th class="font-weight-bold">Created at</th>
                    </tr>
                    </thead>
                    <tbody>
                      <?php if ($projectsList->num_rows > 0): ?>
                      <?php while ($row = $projectsList->fetch_assoc()): ?>
                        <tr>
                          <td>
                            <h6 class="mb-0"><?= htmlspecialchars($row['user_id']) ?></h6>
                          </td>
                          <td>
                            <h6 class="mb-1"><?= htmlspecialchars($row['account_type']) ?></h6>
                          </td>
                          <td>
                            <h6 class="mb-0"></h6>
                          </td>
                          <td>
                            <h6 class="mb-1"><?= htmlspecialchars($row['account_type']) ?></h6>
                          </td>
                          <td>
                            <h6 class="mb-0"></h6>
                          </td>
                          <td>
                            <h6 class="text-muted">
                              <i class="fas fa-circle text-warning-500 text-[10px] ltr:mr-4 rtl:ml-4"></i>
                              <?= htmlspecialchars($row['created_at']) ?>
                            </h6>
                          </td>
                          <td>
                            <a href="./controller/update-status.php?id=<?= $row['user_id'] ?>&action=reject" 
                              class="badge bg-theme-bg-2 text-white text-[12px] mx-2"
                              onclick="return confirm('Are you sure you want to reject this user?')">
                              Edit
                            </a>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    <?php else: ?>
                      <tr><td colspan="8" style="text-align:center;">No projects found.</td></tr>
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
