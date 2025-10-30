<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');
error_reporting(E_ALL);
session_start();
include("../../../config/config.php");
include("../../../services/blockchain.php");
include("../../../utils/session/checkSession.php");
include("../../../DAO/AuditDao.php");
include('../../../DAO/AuditTrailDAO.php');
include("../../../services/IpfsUploader.php");
include("../../../utils/constants/api.php");

$api = new ApiKey();
$jwt = $api->getIpfsApi();

$uploader = new PinataUploader($jwt);
$auditDao = new AuditDAO($conn);

$wallet = $_SESSION["user"]["wallet_address"];
$isRole = hasRole($contract, $auditorRole, $wallet);
if (!$isRole) {
  addAuditor($contract, $adminWallet, $wallet);
}
if (!isset($_GET['title']) || !isset($_GET['record_id'])) {
  die("Invalid access. Please go through the project details page.");
}
$title = isset($_GET['title']) ? htmlspecialchars($_GET['title']) : 'Fetch not working';
$record_id = isset($_GET['record_id']) ? htmlspecialchars($_GET['record_id']) : 'Fetch not working';
$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $user_id = $_SESSION["user"]["id"];
  $summary = trim($_POST["summary"]);
  $result = trim($_POST["result"]); // expect 0,1,2 for PASSED, FLAGGED, REJECTED

  // ✅ Check if this auditor already audited the record
  $check = $conn->prepare("SELECT * FROM audits WHERE record_id = ? AND audit_by = ?");
  $check->bind_param("ii", $record_id, $user_id);
  $check->execute();
  $checkResult = $check->get_result();

  if ($checkResult->num_rows > 0) {
    echo "<script>alert('⚠️ You have already audited this record.');</script>";
    exit;
  }

  // ✅ Validate document upload
  if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
    die("⚠️ No file uploaded or upload error occurred.");
  }

  // 1️⃣ Upload document to IPFS via Pinata
  try {
    $cid = $uploader->uploadDocument($_FILES['document']);
    $documentUrl = $uploader->getGatewayUrl($cid);
    $documentHash = hash_file('sha256', $documentUrl); // hash for integrity
  } catch (Exception $e) {
    die("❌ IPFS upload failed: " . $e->getMessage());
  }

  // 2️⃣ Submit audit to blockchain
  $tx_hash = submitAudit($contract, $wallet, $record_id, $documentHash, $result);

  if (!$tx_hash || str_starts_with($tx_hash, "❌")) {
    die("❌ Blockchain transaction failed: " . htmlspecialchars($tx_hash));
  }

  // 3️⃣ Mirror to database
  $dbSuccess = $auditDao->addAudit(
    $record_id,
    $title,
    $summary,
    $result,
    $documentHash,
    $cid,       // store IPFS CID
    $tx_hash,   // blockchain transaction hash
    $user_id
  );

  if ($dbSuccess) {
    $auditId = $conn->insert_id;
    $trailDao = new AuditTrailDao($conn);
    $trailDao->logAction($auditId, "COMMENTED", "Initial submission of audit", $user_id);
    echo "<script>alert('✅ Audit successfully recorded and stored on blockchain!');</script>";
  } else {
    echo "<script>alert('⚠️ Audit recorded on blockchain but failed to save in database.');</script>";
  }
}
?>
<!doctype html>
<html lang="en" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" dir="ltr"
  data-pc-theme="light">

<head>
  <title>Add Audit</title>
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
            <h5 class="mb-0 font-medium">Add Audit</h5>
          </div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="./dashboard.php">Home</a></li>
            <li class="breadcrumb-item" aria-current="page">add-Audit</li>
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
              <h5>Add Audit & Record Details</h5>
            </div>
            <div class="card-body">
              <form class="form-horizontal" method="POST" enctype="multipart/form-data"> <!-- Form elements -->
                <div class="mb-3">
                  <label for="floatingInput" class="form-label">Project Title:</label>
                  <input type="text" class="form-control" name="title" id="floatingInput" value="<?= $title ?>"
                    <?= $title ? 'readonly' : '' ?> required />
                </div>
                <div class="mb-3">
                  <label for="floatingInput" class="form-label">Record ID:</label>
                  <input type="number" class="form-control" name="record_id" id="floatingInput"
                    value="<?= $record_id ?>" <?= $record_id ? 'readonly' : '' ?> required />

                </div>
                <div class="mb-4">
                  <label for="floatingInput1" class="form-label">Summary:</label>
                  <textarea name="summary" class="form-control" id="description" required
                    placeholder="Enter Audit details"></textarea>
                </div>
                <div class="mb-4">
                  <label for="floatingInput1" class="form-label">Audit Result:</label>
                  <div class="flex">
                    <input type="radio" class="mr-1.5 w-20" name="result" id="floatingInput1" checked="checked"
                      value="0" required />
                    <p>Passed</p>
                    <input type="radio" class="mr-1.5 w-20" name="result" id="floatingInput1" value="1" required />
                    <p>Flagged</p>
                    <input type="radio" class="mr-1.5 w-20" name="result" id="floatingInput1" value="2" required />
                    <p>Rejected</p>
                  </div>
                </div>
                <hr>
                <br>
                <div class="mb-4">
                  <label for="floatingInput1" class="form-label">Audit/Report Document:</label>
                  <input type="file" class="form-control" name="document" id="floatingInput1" required />
                </div>
                <div class="flex mt-1 justify-between items-center flex-wrap">
                  <div class="form-control border-0">
                    <button type="submit" id="submitBtn" disabled
                      class="form-control bg-success-700 text-white mx-auto shadow-2xl">Submit</button>
                    <p class="text-md text-gray-700">Read the disclamer. Submission will only be available after
                      agreeing with the terms.</p>
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
              <h5>⚠️ Audit Submission Disclaimer</h5>
            </div>
            <div class="card-body pc-component break-all whitespace-normal">
              <dl class="grid grid-cols-12 gap-6">
                <dt class="col-span-12 sm:col-span-2 font-semibold">Before submitting your audit report, please
                  carefully read and acknowledge the following:</dt>
                <dd class="col-span-12 sm:col-span-10">
                  <p class="text-md text-gray-700 mb-3">
                    By submitting this report, I affirm that all information, findings, and supporting documents
                    provided are accurate, verified, and submitted in good faith.
                  </p>
                  <hr>
                </dd>
                <dt class="col-span-12 sm:col-span-2 font-semibold">Any falsification, misrepresentation, or deliberate
                  omission of facts is a serious offense and may result in:</dt>
                <dd class="col-span-12 sm:col-span-10">
                  <ul class="list-disc ltr:pl-4 rtl:pr-4 ">
                    <li class="text-md text-gray-700 mb-3">⚖️ Administrative sanctions such as suspension or revocation
                      of system access.</li>
                    <li class="text-md text-gray-700 mb-3">🚔 Legal and administrative penalties under Philippine laws
                      (e.g., R.A. 6713, R.A. 3019, and relevant anti-fraud provisions).</li>
                  </ul>
                  <p class="text-md text-gray-700 mb-3">
                    All submitted reports will be recorded on the blockchain for transparency and accountability.
                    Once submitted, records cannot be altered or deleted.
                  </p>
                </dd>
              </dl>
              <hr>
              <br>
              <div class="flex items-center space-x-2">
                <input type="checkbox" id="confirmDisclaimer" class="w-4 h-4 accent-green-500">
                <p class="text-md text-blue-500">I have reviewed and confirm the accuracy of this record.</p>
              </div>
            </div>
          </div>
        </div>
        <!-- [ sample-page ] end -->
      </div>
      <!-- [ Main Content ] end -->
    </div>
  </div>
  <!-- [ Main Content ] end -->

  <script>
    const checkbox = document.getElementById("confirmDisclaimer");
    const submitBtn = document.getElementById("submitBtn");
    checkbox.addEventListener("change", () => {
      submitBtn.disabled = !checkbox.checked;
    });
  </script>
  <script>
    window.addEventListener("load", function () {
      document.querySelectorAll("textarea").forEach(el => {
        el.style.height = "auto";
        el.style.height = el.scrollHeight + "px";
      });
    });
  </script>
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