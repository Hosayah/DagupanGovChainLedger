<?php
session_start();
include("../../../config/config.php");
include("../../../services/app.php");
include("../../../utils/session/checkSession.php"); // DB connection file

$wallet = $_SESSION["user"]["wallet_address"];
hasRole($contract, $govRole, $wallet);
$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["user"]["user_id"];
    $title = trim($_POST["title"]);
    $category = trim($_POST["category"]);
    $description = trim($_POST["description"]);
    $record_type = trim($_POST["record_type"]);
    $amount = trim($_POST["amount"]);
    $document = trim($_POST["document"]);

    $check = $conn->prepare("SELECT * FROM projects WHERE title = ?");
    $check->bind_param("s", $title);
    $check->execute();
    $checkResult = $check->get_result();
    if ($checkResult->num_rows > 0) {
        $msg = "<script type='text/javascript'>alert('⚠️ Email already registered.');</script>";
        echo $msg;
    } else {
        // Insert into users
        $stmt = $conn->prepare("
          INSERT INTO projects (title, category, description, created_by)
          VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("sssi", $title, $category, $description, $user_id);
        if ($stmt->execute()) {
            $projectId = $conn->insert_id;
            $txRes = submitSpending($contract, $wallet, '0x' . hash('sha256', $document), $record_type, $amount);
            $record = getRecordAsArray($contract, $projectId);
            $recordStmt = $conn->prepare("
              INSERT INTO records (project_id, record_type, amount, document_hash, blockchain_tx)
              VALUES (?, ?, ?, ?)
            ");
            $recordStmt->bind_param("issss", $projectId, $record_type, $amount, $record['doc_hash'], $txRes);
            $recordStmt->execute();
            $msg = "✅ Record Submitted successfully.";
                  
        } else {
           $msg = "❌ Error processing record: " . $stmt->error;
        }
    }
}
?>
<!doctype html>
<html lang="en" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" dir="ltr" data-pc-theme="light">
<head>
  <title>Add Admin</title>
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
            <h5 class="mb-0 font-medium">Add Project</h5>
          </div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="../admin/dashboard.php">Home</a></li>
            <li class="breadcrumb-item" aria-current="page">add-project</li>
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
              <h5>Add Project & Record Detail</h5>
            </div>
            <div class="card-body">
              <form class="form-horizontal" method="POST"> <!-- Form elements -->
                <div class="mb-3">
                  <label for="floatingInput" class="form-label">Project Title:</label>
                  <input type="text" class="form-control" name="title" id="floatingInput" placeholder="E.g Flood Control Project" />
                </div>
                <div class="mb-4">
                  <label for="floatingInput1" class="form-label">Category:</label>
                  <div class="flex">
                    <input type="radio" class="mr-1.5 w-20" name="category" id="floatingInput1" value="Infrastructure" required/> 
                    <p>Infrastructure</p>
                    <input type="radio" class="mr-1.5 w-20" name="category" id="floatingInput1" value="Education" required/> 
                    <p>Education</p>
                    <input type="radio" class="mr-1.5 w-20" name="category" id="floatingInput1" value="Argriculture" required/> 
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
                    <input type="radio" class="mr-1.5 w-20" name="record_type" id="floatingInput1" value="budget" required/> 
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
                  <input type="file" class="form-control" name="document" id="floatingInput1" placeholder="Enter the amount in PHP" required/>
                </div>
                <div class="flex mt-1 justify-between items-center flex-wrap">
                  <div class="form-check">
                    <button type="submit" class="btn btn-primary mx-auto shadow-2xl">Create Admin</button>
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
