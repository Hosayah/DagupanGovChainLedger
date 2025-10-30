<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');
error_reporting(E_ALL);

session_start();
include("../../../config/config.php");
include("../../../services/blockchain.php");
include("../../../utils/session/checkSession.php");
include("../../../services/IpfsUploader.php"); 
include("../../../utils/constants/api.php");

$api = new ApiKey();
$jwt = $api->getIpfsApi();
$uploader = new PinataUploader($jwt);

$wallet = $_SESSION["user"]["wallet_address"];
$isRole = hasRole($contract, $govRole, $wallet);
if (!$isRole) {
  addGovAgency($contract, $adminWallet, $wallet);
}

$count = getCounts($contract);
$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $errors = [];
    $user_id = $_SESSION["user"]["id"];
    $title = trim($_POST["title"]);
    $category = trim($_POST["category"]);
    $description = trim($_POST["description"]);
    $record_type = trim($_POST["record_type"]);
    $amount = trim($_POST["amount"]);

    // ✅ Validate title
    if (empty($title) || strlen($title) < 5 || strlen($title) > 100) {
        $errors[] = "Project title must be between 5 and 100 characters.";
    }

    // ✅ Validate category
    $allowedCategories = ["Infrastructure", "Education", "Agriculture", "Others"];
    if (!in_array($category, $allowedCategories)) {
        $errors[] = "Invalid category selected.";
    }

    // ✅ Validate description
    if (empty($description) || strlen($description) < 10) {
        $errors[] = "Description must be at least 10 characters.";
    }

    // ✅ Validate record type
    $allowedTypes = ["budget", "invoice", "contract"];
    if (!in_array($record_type, $allowedTypes)) {
        $errors[] = "Invalid record type.";
    }

    // ✅ Validate amount
    if (!preg_match('/^\d{1,16}(\.\d{1,2})?$/', $amount)) {
        $errors[] = "Amount must be a valid number with up to 2 decimal places (max 18 digits total).";
    } elseif ((float)$amount <= 0) {
        $errors[] = "Amount must be greater than zero.";
    }

    // ✅ Validate file upload
    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Please upload a valid document file.";
    } else {
        $allowedExts = ['pdf', 'docx', 'jpg', 'jpeg', 'png'];
        $fileExt = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, $allowedExts)) {
            $errors[] = "Invalid file type. Allowed: PDF, DOCX, JPG, PNG.";
        }
        if ($_FILES['document']['size'] > 5 * 1024 * 1024) { // 5MB
            $errors[] = "File must not exceed 5MB.";
        }
    }

    // ✅ Check for duplicate title
    $check = $conn->prepare("SELECT project_id FROM projects WHERE title = ?");
    $check->bind_param("s", $title);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $errors[] = "A project with this title already exists.";
    }

    // 🚫 If validation failed, show all errors
    if (!empty($errors)) {
        $msg = "<script>alert('⚠️ Please correct the following:\\n- " . implode("\\n- ", $errors) . "');</script>";
    } else {
        // ✅ Upload to Pinata
        try {
            $cid = $uploader->uploadDocument($_FILES['document']);
            $documentUrl = $uploader->getGatewayUrl($cid);
        } catch (Exception $e) {
            die("❌ IPFS upload failed: " . $e->getMessage());
        }

        // ✅ Insert project into DB
        $stmt = $conn->prepare("
          INSERT INTO projects (title, category, description, document_path, created_by)
          VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssi", $title, $category, $description, $documentUrl, $user_id);

        if ($stmt->execute()) {
            $projectId = $conn->insert_id;
            $docHash = hash('sha256', $documentUrl);
            $txRes = submitSpending($contract, $wallet, '0x' . $docHash, $record_type);

            $recordStmt = $conn->prepare("
              INSERT INTO records (project_id, record_type, amount, document_hash, document_cid, blockchain_tx, submitted_by)
              VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $recordStmt->bind_param("isssssi", $projectId, $record_type, $amount, $docHash, $cid, $txRes, $user_id);
            $recordStmt->execute();

            $msg = "<script>alert('✅ Project successfully recorded and stored on blockchain!');</script>";
        } else {
            $msg = "<script>alert('❌ Database error: " . addslashes($stmt->error) . "');</script>";
        }
    }
}
?>


<!doctype html>
<html lang="en" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" dir="ltr" data-pc-theme="light">
<head>
  <title>Add Project</title>
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
            <h5 class="mb-0 font-medium">Add Project</h5>
          </div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="./dashboard.php">Home</a></li>
            <li class="breadcrumb-item" aria-current="page">add-project</li>
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
              <h5>Add Project & Record Detail</h5>
            </div>
            <div class="card-body">
              <form class="" method="POST" enctype="multipart/form-data"> <!-- Form elements -->
                <div class="mb-3">
                  <label for="floatingInput" class="form-label">Project Title:</label>
                  <input type="text" class="form-control" name="title" id="floatingInput" placeholder="E.g Flood Control Project" />
                </div>
                <div class="mb-4">
                  <label for="floatingInput1" class="form-label">Category:</label>
                  <div class="flex">
                    <input type="radio" class="mr-1.5 w-20" name="category" id="floatingInput1" value="Infrastructure" checked="checked" required/> 
                    <p>Infrastructure</p>
                    <input type="radio" class="mr-1.5 w-20" name="category" id="floatingInput1" value="Education" required/> 
                    <p>Education</p>
                    <input type="radio" class="mr-1.5 w-20" name="category" id="floatingInput1" value="Agriculture" required/> 
                    <p>Agriculture</p>
                    <input type="radio" class="mr-1.5 w-20" name="category" id="floatingInput1" value="Others" required/> 
                    <p>Other</p>
                  </div>
                </div>
                <div class="mb-4">
                  <label for="floatingInput1" class="form-label">Description:</label>
                  <textarea name="description" class="form-control" id="description" required placeholder="Enter project details"></textarea>
                </div>
               <hr>
               <br>
                <div class="mb-4">
                  <label for="floatingInput1" class="form-label">Record Type:</label>
                  <div class="flex">
                    <input type="radio" class="mr-1.5 w-20" name="record_type" id="floatingInput1" checked="checked" value="budget" required/> 
                    <p>Budget</p>
                    <input type="radio" class="mr-1.5 w-20" name="record_type" id="floatingInput1" value="invoice" required/> 
                    <p>Invoice</p>
                    <input type="radio" class="mr-1.5 w-20" name="record_type" id="floatingInput1" value="contract" required/> 
                    <p>Contract</p>
                  </div>
                </div>
                <div class="mb-4">
                  <label for="floatingInput1" class="form-label">Amount:</label>
                  <input type="number" class="form-control" name="amount" id="floatingInput1" placeholder="Enter the amount in PHP" required/>
                </div>
                <div class="mb-4">
                  <label for="floatingInput1" class="form-label">Document:</label>
                  <input type="file" class="form-control" name="document" id="floatingInput1" placeholder="Upload the Project document/Invoice" required/>
                </div>
                <div class="flex mt-1 justify-between items-center flex-wrap">
                  <div class="form-control border-0">
                    <button type="submit" id="submitBtn" disabled class="form-control text-white bg-success-700 shadow-2xl">Submit</button>
                    <p class="text-md text-gray-700 mb-3">Read the disclamer. Submission will only be available after agreeing with the terms.</p>
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
        <div class="col-span-12 md:col-span-6">
          <div class="card">
            <div class="card-header">
              <h5>⚠️ Disclaimer: Record Submission Notice</h5>
            </div>
            <div class="card-body pc-component break-all whitespace-normal">
              <dl class="grid grid-cols-12 gap-6">
                <dt class="col-span-12 sm:col-span-2 font-semibold">Please Read:</dt>
                <dd class="col-span-12 sm:col-span-10">
                  <p class="text-md text-gray-700 mb-3">
                    By submitting this record, I hereby certify that all information and attached documents are
                    <strong>true, accurate, and complete</strong> to the best of my knowledge. I acknowledge that this submission
                    will be <strong>hashed and stored on the blockchain</strong>, making it permanently verifiable and immutable.
                    Any falsified or misleading information may result in administrative or legal actions in accordance with
                    government transparency and anti-corruption policies.
                  </p>
                  <hr>
                </dd>
                <dt class="col-span-12 sm:col-span-2 font-semibold">Possible Consequences:</dt>
                <dd class="col-span-12 sm:col-span-10">
                  <ul class="list-disc ltr:pl-4 rtl:pr-4 ">
                    <li class="text-md text-gray-700 mb-3">❌ Immediate rejection or invalidation of the submitted record.</li>
                    <li class="text-md text-gray-700 mb-3">⚖️ Administrative sanctions such as suspension or revocation of system access.</li>
                    <li class="text-md text-gray-700 mb-3">📜 Disciplinary actions under the agency’s code of conduct.</li>
                    <li class="text-md text-gray-700 mb-3">💰 Financial and legal liabilities for falsified or fraudulent entries.</li>
                    <li class="text-md text-gray-700 mb-3">🚔 Criminal prosecution under relevant anti-corruption and falsification laws (e.g., Revised Penal Code, RA 3019 – Anti-Graft and Corrupt Practices Act).</li>
                  </ul>
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

<!-- Required Js -->
 <script>
  const checkbox = document.getElementById("confirmDisclaimer");
  const submitBtn = document.getElementById("submitBtn");
  checkbox.addEventListener("change", () => {
    submitBtn.disabled = !checkbox.checked;
  });
</script>
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
