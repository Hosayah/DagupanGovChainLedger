<?php
session_start();
include("../../../config/config.php");
include("../../../utils/session/checkSession.php");
include("../../../DAO/ProjectDao.php");
include("../../../DAO/RecordDao.php");
include("../../../DAO/AuditDao.php");
include("../../../DAO/UserDao.php");
include("../../../DAO/AuditTrailDAO.php");
include("../../../services/IpfsUploader.php");
require '../../../vendor/autoload.php';
include("../../../utils/constants/api.php");
include("./controller/tablePageController.php");


$api = new ApiKey();
$jwt = $api->getIpfsApi();
$ipfsUploader = new PinataUploader($jwt);
$projectDao = new ProjectDao($conn);
$userDao = new UserDao($conn);
$auditDao = new AuditDao($conn);
$trailDao = new AuditTrailDao($conn);

// Validate input
if (!isset($_GET['id']) || !isset($_GET['action'])) {
  die("Invalid request.");
}

$audit_id = intval($_GET['id']);
$action = $_GET['action'];

if ($action != 'edit') {
  die("Invalid action.");
}

$result = $auditDao->getAuditById($audit_id) ?? [];
//$record = $recordDao->getRecordByProjectId($result['record_id']) ?? [];
$type = $result['type'] ?? '';
$link = $ipfsUploader->getGatewayUrl($result['document_cid']);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $audit_id = $result["audit_id"];
  $title = trim(preg_replace('/\s+/',' ',$_POST["title"]));
  $summary = trim(preg_replace('/\s+/', ' ', $_POST["summary"]));

  // Check for existing title
  $check = $conn->prepare("SELECT * FROM audits WHERE title = ? AND audit_id != ?");
  $check->bind_param("ss", $title, $audit_id);
  $check->execute();
  $checkResult = $check->get_result();
  if ($checkResult->num_rows > 0) {
    $msg = "<script>alert('⚠️ Audit title already exist. Please indicate on the title that it has been updated.');</script>";
    echo $msg;
  } else {
    // update project into database
    if ($auditDao->updateAudit($audit_id, $title, $summary)) {
      
      $trailDao->logComment($audit_id, "Audit details updated", $_SESSION['user']['id']);
      header("Location: edit-audit.php?id=$audit_id&action=edit&updated=1");
      exit;
    } else {
      echo "<script>alert('❌ Error inserting project');</script>";
    }
  }
}

if (!isset($_SESSION['limit'])) {
  $_SESSION['limit'] = 0;
}


$limit = $_SESSION['limit'];
$user_id = $_SESSION["user"]["id"];
$auditDao = new AuditDAO($conn);
$search_term = $_GET['search_term'] ?? '';
$auditList = $trailDao->getTrailByAuditIdWithSearch($result['audit_id'],$limit, $search_term);
?>

<!doctype html>
<html lang="en" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" dir="ltr"
  data-pc-theme="light">

<head>
  <title>Edit Project Details</title>
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
  <link rel="stylesheet" type="text/css"
    href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
  <link rel="stylesheet" type="text/css"
    href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css" />
  <link rel="stylesheet" href="../../src/output.css">
</head>

<body>
  <!-- [ Pre-loader ] start -->
  <div class="loader-bg fixed inset-0 bg-white dark:bg-themedark-cardbg z-[1034]">
    <div class="loader-track h-[5px] w-full inline-block absolute overflow-hidden top-0">
      <div
        class="loader-fill w-[300px] h-[5px] bg-primary-500 absolute top-0 left-0 animate-[hitZak_0.6s_ease-in-out_infinite_alternate]">
      </div>
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
            <h5 class="mb-0 font-medium">Edit Audit Details</h5>
          </div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="../admin/dashboard.php">Home</a></li>
            <li class="breadcrumb-item" aria-current="page">View-audit</li>
            <li class="breadcrumb-item" aria-current="page">edit-audit</li>
          </ul>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->

      <!-- [ Main Content ] start -->
      <div class="grid grid-cols-12 gap-x-6">
        <!-- [ sample-page ] start -->
        <div class="col-span-12 md:col-span-6">
          <div class="card">
            <div class="card-header">
              <h5>Edit Audit Details</h5>
            </div>
            <div class="card-body">
              <form class="form-horizontal" method="POST" enctype="multipart/form-data"> <!-- Form elements -->
                <div class="mb-3">
                  <label for="floatingInput" class="form-label">Audit Title:</label>
                  <input type="text" class="form-control" name="title" id="floatingInput"
                    placeholder="E.g Flood Control Audit" value="<?= htmlspecialchars($result['title']) ?>" required />
                </div>
                <div class="mb-4">
                  <label for="floatingInput1" class="form-label">Summary:</label>
                  <textarea name="summary" class="form-control" id="description"
                    value="<?= htmlspecialchars($result['summary']) ?>" required
                    placeholder="Enter Audit details"><?= htmlspecialchars($result['summary']) ?></textarea>
                </div>
                <hr>
                <br>
                <div class="flex mt-1 justify-between items-center flex-wrap">
                  <div class="form-control border-0">
                    <button type="submit" id="submitBtn"
                      class="form-control bg-success-700 text-white mx-auto shadow-2xl">Save</button>
                    <?php if (!empty($msg)): ?>
                      <p id="msg"
                        class="text-sm mb-3 <?= strpos($msg, '✅') !== false ? 'text-green-600' : 'text-red-600' ?>">
                        <?= $msg ?>
                      </p>
                    <?php endif; ?>
                  </div>
                </div>
              </form> <!-- Form ends -->
            </div>
          </div>
        </div>
        <div class="col-span-12 md:col-span-6">
          <div class="card">
            <div class="card-header">
              <h5>Final Report/Audit Immutable Details</h5>
            </div>
            <div class="card-body pc-component break-all whitespace-normal">
              <dl class="grid grid-cols-12 gap-6">
                <dt class="col-span-12 sm:col-span-3 font-semibold">Audit ID:</dt>
                <dd class="col-span-12 sm:col-span-9">AU-ID-<?= htmlspecialchars($result['audit_id']) ?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Record ID:</dt>
                <dd class="col-span-12 sm:col-span-9">R-ID-<?= htmlspecialchars($result['record_id']) ?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Audit By:</dt>
                <dd class="col-span-12 sm:col-span-9">USER-ID-<?= htmlspecialchars($result['audit_by']) ?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Audit Result:</dt>
                <dd class="col-span-12 sm:col-span-9"><?= htmlspecialchars(string: $result['result']) ?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Document Hash:</dt>
                <dd class="col-span-12 sm:col-span-9 min-w-0 max-w-[16rem] break-all whitespace-normal"><textarea
                    class="w-full shadow-none border-0" style="resize: none;"
                    readonly><?= htmlspecialchars($result['document_hash']) ?> </textarea><a class="text-blue-500"
                    target="_blank" href="<?= htmlspecialchars($link) ?>">Click here to view document </a></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Blockchain TX:</dt>
                <dd class="col-span-12 sm:col-span-9 min-w-0 max-w-[16rem] break-all whitespace-normal"><textarea
                    class="w-full shadow-none border-0" style="resize: none;"
                    readonly><?= htmlspecialchars($result['tx_hash']) ?></textarea></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Date & Time:</dt>
                <dd class="col-span-12 sm:col-span-9"><?= htmlspecialchars($result['audited_at']) ?></dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="col-span-12">
          <div class="card">
            <div class="card-header flex justify-between">
              <h5 class="mt-4">Recent Trail</h5>
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
                        <th class="font-weight-bold">Trail ID</th>
                        <th class="font-weight-bold">Audit ID</th>
                        <th class="font-weight-bold">Action</th>
                        <th class="font-weight-bold">Note</th>
                        <th class="font-weight-bold">Performed By:</th>
                        <th class="font-weight-bold">Submitted at</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if ($auditList->num_rows > 0): ?>
                        <?php while ($row = $auditList->fetch_assoc()): ?>
                          <tr>
                            <td>
                              <h6 class="mb-0">AUTR-ID-<?= htmlspecialchars($row['trail_id']) ?></h6>
                            </td>
                            <td>
                              <h6 class="mb-1">AU-ID-<?= htmlspecialchars($row['audit_id']) ?></h6>
                            </td>
                            <td>
                              <h6 class="mb-0"><?= htmlspecialchars($row['action']) ?></h6>
                            </td>
                            <td>
                              <h6 class="mb-1"><?= htmlspecialchars($row['note']) ?></h6>
                            </td>
                            <td>
                              <h6 class="mb-0">USER-ID-<?= htmlspecialchars($row['performed_by']) ?></h6>
                            </td>
                            <td>
                              <h6 class="mb-1"><?= htmlspecialchars($row['created_at']) ?></h6>
                            </td>
                          </tr>
                        <?php endwhile; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="8" style="text-align:center;">No Audits found.</td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
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
  <script>
    window.addEventListener("load", function () {
      document.querySelectorAll("textarea").forEach(el => {
        el.style.height = "auto";
        el.style.height = el.scrollHeight + "px";
      });
    });
  </script>

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