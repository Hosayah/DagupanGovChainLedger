<?php
session_start();
include("../../../config/config.php");
include("../../../utils/session/checkSession.php");
include("../../../DAO/ProjectDao.php");
include("../../../DAO/RecordDao.php");
include("../../../DAO/AuditDao.php");
include("../../../DAO/UserDao.php");
include("../../../services/IpfsUploader.php");

$jwt = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VySW5mb3JtYXRpb24iOnsiaWQiOiI5ZGM2N2E5Mi0wMmUzLTRkYzAtYjQ5Yy0zOTUyMmY3NzU4NTgiLCJlbWFpbCI6ImNhdGFiYXlqb3NpYWgxOUBnbWFpbC5jb20iLCJlbWFpbF92ZXJpZmllZCI6dHJ1ZSwicGluX3BvbGljeSI6eyJyZWdpb25zIjpbeyJkZXNpcmVkUmVwbGljYXRpb25Db3VudCI6MSwiaWQiOiJGUkExIn0seyJkZXNpcmVkUmVwbGljYXRpb25Db3VudCI6MSwiaWQiOiJOWUMxIn1dLCJ2ZXJzaW9uIjoxfSwibWZhX2VuYWJsZWQiOmZhbHNlLCJzdGF0dXMiOiJBQ1RJVkUifSwiYXV0aGVudGljYXRpb25UeXBlIjoic2NvcGVkS2V5Iiwic2NvcGVkS2V5S2V5IjoiMDJiODlmNzNmYWY3ODhmOTBlNjYiLCJzY29wZWRLZXlTZWNyZXQiOiIzY2UxNzE3YmZkYjRlOTgzZjRjMmJmYzllYWMwMTM5NWQxMmM0YWQyMTQ4M2RkMWU2OWMzZmYxNmNmMzM3ZjFjIiwiZXhwIjoxNzkxNzA3MTUyfQ.uqpmqJ8qMpGe8-O6l3sQlYrs0wToLZKJBiLhJqH7hZ4"; 
$ipfsUploader = new PinataUploader($jwt);
$projectDao = new ProjectDao($conn);
$userDao = new UserDao($conn);
$auditDao = new AuditDao($conn);

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
$auditor = $userDao->getUserByIdFromAuditor($result["audit_by"]);
//$record = $recordDao->getRecordByProjectId($result['record_id']) ?? [];
$type = $result['type'] ??'';
$link = $ipfsUploader->getGatewayUrl($result['document_cid']);


?>

<!doctype html>
<html lang="en" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" dir="ltr" data-pc-theme="light">
<head>
  <title>View Audit Details</title>
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
    <link rel="stylesheet" href="../../src/output.css">
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
              <h5>Audit Basic Details</h5>
            </div>
            <div class="card-body">
              <dl class="grid grid-cols-12 gap-6">
                <dt class="col-span-12 sm:col-span-3 font-semibold">Audit Title:</dt>
                <dd class="col-span-12 sm:col-span-9">AU-ID-<?= htmlspecialchars($result['title'])?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Audit Summary:</dt>
                <dd class="col-span-12 sm:col-span-9 min-w-0 max-w-[16rem] break-all whitespace-normal"><textarea class="w-full shadow-none border-0" style="resize: none;" readonly><?= htmlspecialchars($result['summary'])?></textarea></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Office Name:</dt>
                <dd class="col-span-12 sm:col-span-9"><?= htmlspecialchars($auditor['organization_name'])?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Office Code:</dt>
                <dd class="col-span-12 sm:col-span-9"><?= htmlspecialchars($auditor['office_code'])?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Accredition No.:</dt>
                <dd class="col-span-12 sm:col-span-9"><?= htmlspecialchars($auditor['accreditation_number'])?></dd>
              </dl>
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
                <dd class="col-span-12 sm:col-span-9">AU-ID-<?= htmlspecialchars($result['audit_id'])?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Record ID:</dt>
                <dd class="col-span-12 sm:col-span-9">R-ID-<?= htmlspecialchars($result['record_id'])?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Audit By:</dt>
                <dd class="col-span-12 sm:col-span-9">USER-ID-<?= htmlspecialchars($result['audit_by'])?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Audit Result:</dt>
                <dd class="col-span-12 sm:col-span-9"><?= htmlspecialchars(string: $result['result'])?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Document Hash:</dt>
                <dd class="col-span-12 sm:col-span-9 min-w-0 break-all whitespace-normal"><textarea class="w-full shadow-none border-0" style="resize: none;" readonly><?= htmlspecialchars($result['document_hash'])?> </textarea><a class="text-blue-500" target="_blank" href="<?= htmlspecialchars($link)?>">Click here to view document </a></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Blockchain TX:</dt>
                <dd class="col-span-12 sm:col-span-9 min-w-0 break-all whitespace-normal"><textarea class="w-full shadow-none border-0" style="resize: none;" readonly><?= htmlspecialchars($result['tx_hash'])?></textarea></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Date & Time:</dt>
                <dd class="col-span-12 sm:col-span-9"><?= htmlspecialchars($result['audited_at'])?></dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="col-span-12">
          <div class="card">
            <div class="card-header">
              <h5>AI Overview</h5>
            </div>
            <div class="card-body">
              <p class="text-md text-gray-700 mb-3">
                The Dagupan River Flood Control Improvement Project is a strategic initiative by the Department of Public Works and Highways (DPWH) aimed at strengthening flood protection infrastructure along the Pantal and Calmay Rivers in Dagupan City, Pangasinan. This project was developed in response to the city’s recurring flooding issues that have historically impacted both commercial and residential areas.
              </p>
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
window.addEventListener("load", function() {
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
