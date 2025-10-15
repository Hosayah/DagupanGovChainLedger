<?php
session_start();
include("../../../config/config.php");
include("../../../utils/session/checkSession.php");
include("../../../DAO/ProjectDao.php");
include("../../../DAO/RecordDao.php");
include("../../../DAO/AuditDao.php");
include("../../../services/IpfsUploader.php");

$jwt = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VySW5mb3JtYXRpb24iOnsiaWQiOiI5ZGM2N2E5Mi0wMmUzLTRkYzAtYjQ5Yy0zOTUyMmY3NzU4NTgiLCJlbWFpbCI6ImNhdGFiYXlqb3NpYWgxOUBnbWFpbC5jb20iLCJlbWFpbF92ZXJpZmllZCI6dHJ1ZSwicGluX3BvbGljeSI6eyJyZWdpb25zIjpbeyJkZXNpcmVkUmVwbGljYXRpb25Db3VudCI6MSwiaWQiOiJGUkExIn0seyJkZXNpcmVkUmVwbGljYXRpb25Db3VudCI6MSwiaWQiOiJOWUMxIn1dLCJ2ZXJzaW9uIjoxfSwibWZhX2VuYWJsZWQiOmZhbHNlLCJzdGF0dXMiOiJBQ1RJVkUifSwiYXV0aGVudGljYXRpb25UeXBlIjoic2NvcGVkS2V5Iiwic2NvcGVkS2V5S2V5IjoiMDJiODlmNzNmYWY3ODhmOTBlNjYiLCJzY29wZWRLZXlTZWNyZXQiOiIzY2UxNzE3YmZkYjRlOTgzZjRjMmJmYzllYWMwMTM5NWQxMmM0YWQyMTQ4M2RkMWU2OWMzZmYxNmNmMzM3ZjFjIiwiZXhwIjoxNzkxNzA3MTUyfQ.uqpmqJ8qMpGe8-O6l3sQlYrs0wToLZKJBiLhJqH7hZ4"; 
$ipfsUploader = new PinataUploader($jwt);
$projectDao = new ProjectDao($conn);
$recordDao = new RecordDao($conn);
$auditDao = new AuditDao($conn);

// Validate input
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    die("Invalid request.");
}

$project_id = intval($_GET['id']);
$action = $_GET['action'];

if ($action != 'edit') {
    die("Invalid action.");
}

$result = $projectDao->getProjectById($project_id) ?? [];
$record = $recordDao->getRecordByProjectId($project_id ?? []);
$category = $result["category"] ?? '';
$type = $result['type'] ??'';
$link = $ipfsUploader->getGatewayUrl($record['document_cid']);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $project_id = $result["project_id"];
    $title = trim($_POST["title"]);
    $category = trim($_POST["category"]);
    $description = trim($_POST["description"]);

    // Check for existing title
    $check = $conn->prepare("SELECT * FROM projects WHERE title = ?");
    $check->bind_param("s", $title);
    $check->execute();
    $checkResult = $check->get_result();
    if ($checkResult->num_rows > 0) {
        $msg = "<script>alert('⚠️ Project title already exists.');</script>";
        echo $msg;
    } else {
        // update project into database
        if ($projectDao->updateProject($title, $category, $description, $project_id)) {
            header("Location: edit-project.php?id=$project_id&action=edit&updated=1");
            exit;
        } else {
            echo "<script>alert('❌ Error inserting project');</script>";
        }
    }
}
?>

<!doctype html>
<html lang="en" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" dir="ltr" data-pc-theme="light">
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
            <h5 class="mb-0 font-medium">Edit Project Details</h5>
          </div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="../admin/dashboard.php">Home</a></li>
            <li class="breadcrumb-item" aria-current="page">View-projects</li>
            <li class="breadcrumb-item" aria-current="page">edit-project</li>
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
                  <input type="text" class="form-control" name="title" id="floatingInput" value="<?= htmlspecialchars($result['title'])?>" placeholder="E.g Flood Control Project" />
                </div>
                <div class="mb-4">
                  <label for="floatingInput1" class="form-label">Category:</label>
                  <div class="flex">
                    <input type="radio" class="mr-1.5 w-20" name="category" id="floatingInput1" value="Infrastructure" <?= htmlspecialchars($category == 'Infrastructure' ? 'checked':'')?> required/> 
                    <p>Infrastructure</p>
                    <input type="radio" class="mr-1.5 w-20" name="category" id="floatingInput1" value="Education" <?= htmlspecialchars($category == 'Education' ? 'checked':'')?> required/> 
                    <p>Education</p>
                    <input type="radio" class="mr-1.5 w-20" name="category" id="floatingInput1" value="Argriculture" <?= htmlspecialchars($category == 'Agriculture' ? 'checked':'')?> required/> 
                    <p>Agriculture</p>
                    <input type="radio" class="mr-1.5 w-20" name="category" id="floatingInput1" value="Others" <?= htmlspecialchars($category == 'Others' ? 'checked':'')?> required/> 
                    <p>Other</p>
                  </div>
                </div>
                <div class="mb-4">
                  <label for="floatingInput1" class="form-label">Description:</label>
                  <textarea name="description" class="form-control" id="description" required placeholder="Enter project details"><?= htmlspecialchars($result['description'])?></textarea>
                </div>
               <hr>
               <br>
                <div class="flex mt-1 justify-between items-center flex-wrap">
                  <div class="form-control border-0">
                    <button type="submit" id="submitBtn" class="form-control text-white bg-success-700 shadow-2xl">Submit</button>
                    
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
              <h5>Immutable Details (Transaction Metadata)</h5>
            </div>
            <div class="card-body pc-component break-all whitespace-normal">
              <dl class="grid grid-cols-12 gap-6">
                <dt class="col-span-12 sm:col-span-3 font-semibold">Record ID:</dt>
                <dd class="col-span-12 sm:col-span-9">R-ID-<?= htmlspecialchars($record['record_id'])?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Project ID:</dt>
                <dd class="col-span-12 sm:col-span-9">PR-ID-<?= htmlspecialchars($record['project_id'])?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Submitted By:</dt>
                <dd class="col-span-12 sm:col-span-9">USER-ID-<?= htmlspecialchars($record['submitted_by'])?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Record Type:</dt>
                <dd class="col-span-12 sm:col-span-9"><?= htmlspecialchars(string: $record['record_type'])?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Amount:</dt>
                <dd class="col-span-12 sm:col-span-9">₱ <?= htmlspecialchars(number_format($record['amount'], 2, '.', ','))?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Document Hash:</dt>
                <dd class="col-span-12 sm:col-span-9 min-w-0 max-w-[16rem] break-all whitespace-normal"><textarea class="w-full shadow-none border-0" style="resize: none;" readonly><?= htmlspecialchars($record['document_hash'])?> </textarea><a class="text-blue-500" target="_blank" href="<?= htmlspecialchars($link)?>">Click here to view document </a></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Blockchain TX:</dt>
                <dd class="col-span-12 sm:col-span-9 min-w-0 max-w-[16rem] break-all whitespace-normal"><textarea class="w-full shadow-none border-0" style="resize: none;" readonly><?= htmlspecialchars($record['blockchain_tx'])?></textarea></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Date:</dt>
                <dd class="col-span-12 sm:col-span-9">Submitted At:<?= htmlspecialchars($record['submitted_at'])?></dd>
              </dl>
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
