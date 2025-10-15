<?php
session_start();
include("../../../config/config.php");
include("../../../utils/session/checkSession.php");
include("../../../DAO/ProjectDao.php");
include("../../../DAO/RecordDao.php");
include("../../../DAO/AuditDao.php");
include("../../../DAO/UserDao.php");
include("../../../services/blockchain.php");
include("../../../services/IpfsUploader.php");


$jwt = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VySW5mb3JtYXRpb24iOnsiaWQiOiI5ZGM2N2E5Mi0wMmUzLTRkYzAtYjQ5Yy0zOTUyMmY3NzU4NTgiLCJlbWFpbCI6ImNhdGFiYXlqb3NpYWgxOUBnbWFpbC5jb20iLCJlbWFpbF92ZXJpZmllZCI6dHJ1ZSwicGluX3BvbGljeSI6eyJyZWdpb25zIjpbeyJkZXNpcmVkUmVwbGljYXRpb25Db3VudCI6MSwiaWQiOiJGUkExIn0seyJkZXNpcmVkUmVwbGljYXRpb25Db3VudCI6MSwiaWQiOiJOWUMxIn1dLCJ2ZXJzaW9uIjoxfSwibWZhX2VuYWJsZWQiOmZhbHNlLCJzdGF0dXMiOiJBQ1RJVkUifSwiYXV0aGVudGljYXRpb25UeXBlIjoic2NvcGVkS2V5Iiwic2NvcGVkS2V5S2V5IjoiMDJiODlmNzNmYWY3ODhmOTBlNjYiLCJzY29wZWRLZXlTZWNyZXQiOiIzY2UxNzE3YmZkYjRlOTgzZjRjMmJmYzllYWMwMTM5NWQxMmM0YWQyMTQ4M2RkMWU2OWMzZmYxNmNmMzM3ZjFjIiwiZXhwIjoxNzkxNzA3MTUyfQ.uqpmqJ8qMpGe8-O6l3sQlYrs0wToLZKJBiLhJqH7hZ4"; 
$ipfsUploader = new PinataUploader($jwt);
$projectDao = new ProjectDao($conn);
$recordDao = new RecordDao($conn);
$auditDao = new AuditDao($conn);
$userDao = new UserDao($conn);

// Validate input
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    die("Invalid request.");
}

$project_id = intval($_GET['id']);
$action = $_GET['action'];

if ($action != 'view') {
    die("Invalid action.");
}


$result = $projectDao->getProjectById($project_id) ?? [];
$record = $recordDao->getRecordByProjectId($project_id ?? []);
$auditList = $auditDao->getAuditByRecordId($record["record_id"]);
$block = getRecordAsArray($contract, $record['record_id']);
//echo $block['doc_hash'];
//echo '0x' . $record['document_hash'];
$docHash = '0x' . hash('sha256', $result['document_path']);
$verified = ($docHash === $block['doc_hash']) ? '<abbr title="The record document has not been tampered" class="text-decoration-line: none;"><i class="ph-fill text-green-600 text-2xl ph-seal-check"></i></abbr>' : '<abbr title="The record document has been tampered." class="text-decoration-line: none;"><i class="ph-fill text-yellow-400 text-2xl ph-seal-warning"></i></abbr>';
$userResult = $userDao->getUserByIdFromAgency($result["created_by"]) ?? [];
$category = $result["category"] ?? '';
$type = $result['type'] ??'';
$link = $ipfsUploader->getGatewayUrl($record['document_cid']);

$pdf_data = file_get_contents($link);
$temp_pdf = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';
file_put_contents($temp_pdf, $pdf_data);

// Path to Poppler binary (if not in PATH)
$poppler_path = "C:\\poppler-25.07.0\\Library\\bin\\pdftotext.exe";

// Output file
$output_txt = $temp_pdf . ".txt";

// Execute Poppler command
exec("\"$poppler_path\" \"$temp_pdf\" \"$output_txt\"");

// Read the extracted text
$text = file_get_contents($output_txt);

$context = $text . '<br>'. 
print_r( $result, true) . '<br>Blockchain details: ' . 
print_r( $block, true) .'<br>' .
print_r($record, true);
//echo $context;

$auditCounter = $auditDao->getAuditCountersByRecordId($record['record_id']);
  
$totalAudits = $auditCounter['total'] > 0 ? $auditCounter['total'] : 1;
$totalPassed = $auditCounter['passed'] > 0 ? $auditCounter['passed'] : 1;
$totalFlagged = $auditCounter['flagged'] > 0 ? $auditCounter['flagged'] : 1;
$totalRejected = $auditCounter['rejected'] > 0 ? $auditCounter['rejected'] : 1;
  
$passedPercentage   = round(($auditCounter['passed']   / $totalAudits) * 100, 2);
$flaggedPercentage   = round(($auditCounter['flagged']   / $totalAudits) * 100, 2);
$rejectedPercentage   = round(($auditCounter['rejected']   / $totalAudits) * 100, 2);
?>

<!doctype html>
<html lang="en" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" dir="ltr" data-pc-theme="light">
<head>
  <title>view Project Details</title>
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
    <link rel="stylesheet" href="../assets/css/chatbox.css" id="main-style-link" />
    <link rel="stylesheet" href="../assets/css/landing-page.css" id="main-style-link" />
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
            <h5 class="mb-0 font-medium">View Project Details</h5>
          </div>
          <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="../admin/dashboard.php">Home</a></li>
            <li class="breadcrumb-item" aria-current="page">View-projects</li>
            <li class="breadcrumb-item" aria-current="page">view-project</li>
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
              <h5>Project Details</h5>
            </div>
            <div class="card-body pc-component break-all whitespace-normal">
              <dl class="grid grid-cols-12 gap-6">
                <dt class="col-span-12 sm:col-span-3 font-semibold">Project Title:</dt>
                <dd class="col-span-12 sm:col-span-9"><?= htmlspecialchars($result['title'])?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Category:</dt>
                <dd class="col-span-12 sm:col-span-9"><?= htmlspecialchars($result['category'])?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Description:</dt>
                <dd class="col-span-12 sm:col-span-9 min-w-0 break-all whitespace-normal"><textarea class="w-full shadow-none border-0" style="resize: none;" readonly><?= htmlspecialchars($result['description'])?> </textarea></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Document Path:</dt>
                <dd class="col-span-12 sm:col-span-9 min-w-0 break-all whitespace-normal"><textarea class="w-full shadow-none border-0" style="resize: none;" readonly><?= htmlspecialchars($result['document_path'])?> </textarea><a class="text-blue-500" target="_blank" href="<?= htmlspecialchars($link)?>">Click here to view document </a></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Submitted By:</dt>
                <dd class="col-span-12 sm:col-span-9"><?= htmlspecialchars($userResult['agency_name'])?></dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="col-span-12">
          <div class="card">
            <div class="card-header flex justify-between">
              <h5>Record Metadata</h5>
              <?= $verified ?>
            </div>
            <div class="card-body pc-component break-all whitespace-normal">
              <dl class="grid grid-cols-12 gap-6">
                <dt class="col-span-12 sm:col-span-3 font-semibold">Record ID:</dt>
                <dd class="col-span-12 sm:col-span-9">R-ID-<?= htmlspecialchars($record['record_id'])?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Project ID:</dt>
                <dd class="col-span-12 sm:col-span-9">PR-ID-<?= htmlspecialchars($record['project_id'])?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Submitted By:</dt>
                <dd class="col-span-12 sm:col-span-9">AGENCY-ID-<?= htmlspecialchars($userResult['agency_id'])?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Record Type:</dt>
                <dd class="col-span-12 sm:col-span-9"><?= htmlspecialchars(string: $record['record_type'])?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Amount:</dt>
                <dd class="col-span-12 sm:col-span-9">₱ <?= htmlspecialchars(number_format($record['amount'], 2, '.', ','))?></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Document Hash:</dt>
                <dd class="col-span-12 sm:col-span-9 min-w-0 break-all whitespace-normal"><textarea class="w-full shadow-none border-0" style="resize: none;" readonly><?= htmlspecialchars($record['document_hash'])?> </textarea></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Blockchain TX:</dt>
                <dd class="col-span-12 sm:col-span-9 min-w-0 break-all whitespace-normal"><textarea class="w-full shadow-none border-0" style="resize: none;" readonly><?= htmlspecialchars($record['blockchain_tx'])?></textarea></dd>
                <dt class="col-span-12 sm:col-span-3 font-semibold">Date & Time:</dt>
                <dd class="col-span-12 sm:col-span-9"><?= htmlspecialchars($record['submitted_at'])?></dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="col-span-12 xl:col-span-4 md:col-span-6">
            <div class="card user-list">
              <div class="card-header">
                <h5>Audit Results</h5>
              </div>
              <div class="card-body">
                <div class="flex items-center justify-between gap-2 mb-2">
                  <h6 class="flex items-center gap-1">
                    <i class="ti ti-star-filled text-[10px] mr-2.5 text-warning-500"></i>
                    Passed
                  </h6>
                  <h6><?= htmlspecialchars($auditCounter['passed'])?></h6>
                </div>
                <div class="w-full bg-theme-bodybg rounded-lg h-1.5 mb-6 mt-3 dark:bg-themedark-bodybg">
                  <div
                    class="bg-theme-bg-1 h-full rounded-lg shadow-[0_10px_20px_0_rgba(0,0,0,0.3)]"
                    role="progressbar"
                    style="width: <?= htmlspecialchars($passedPercentage)?>%"
                  ></div>
                </div>

                <div class="flex items-center justify-between gap-2 mb-2">
                  <h6 class="flex items-center gap-1">
                    <i class="ti ti-star-filled text-[10px] mr-2.5 text-warning-500"></i>
                    Flagged
                  </h6>
                  <h6><?= htmlspecialchars($auditCounter['flagged'])?></h6>
                </div>
                <div class="w-full bg-theme-bodybg rounded-lg h-1.5 mb-6 mt-3 dark:bg-themedark-bodybg">
                  <div
                    class="bg-theme-bg-1 h-full rounded-lg shadow-[0_10px_20px_0_rgba(0,0,0,0.3)]"
                    role="progressbar"
                    style="width: <?= htmlspecialchars($flaggedPercentage)?>%"
                  ></div>
                </div>
                <div class="flex items-center justify-between gap-2 mb-2">
                  <h6 class="flex items-center gap-1">
                    <i class="ti ti-star-filled text-[10px] mr-2.5 text-warning-500"></i>
                    Rejected
                  </h6>
                  <h6><?= htmlspecialchars($auditCounter['rejected'])?></h6>
                </div>
                <div class="w-full bg-theme-bodybg rounded-lg h-1.5 mb-6 mt-3 dark:bg-themedark-bodybg">
                  <div
                    class="bg-theme-bg-1 h-full rounded-lg shadow-[0_10px_20px_0_rgba(0,0,0,0.3)]"
                    role="progressbar"
                    style="width: <?= htmlspecialchars($rejectedPercentage)?>%"
                  ></div>
                </div>
              </div>
            </div>
        </div>
        <div class="col-span-12 xl:col-span-8 md:col-span-6">
          <div class="card">
            <div class="card-header">
              <h5>Recent Audits</h5>
            </div>
            <div class="card-body">
              <form class="form-horizontal" method="POST"> <!-- Form elements -->
                <div class="mb-3 flex">
                </div>
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                    <tr class="bg-dark text-white text-center font-weight-bold">
                      <th class="font-weight-bold">Audit ID</th>
                      <th class="font-weight-bold">Title</th>
                      <th class="font-weight-bold">Record ID</th>
                      <th class="font-weight-bold">Result</th>
                      <th class="font-weight-bold">Submitted By</th>
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
                        </tr>
                      <?php endwhile; ?>
                    <?php else: ?>
                      <tr><td colspan="8" style="text-align:center;">No Audits found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </form> <!-- Form ends -->
            </div>
          </div>
        </div>
        <div class="col-span-12">
          <div class="card">
            <div class="card-header">
              <h5>AI Assitant</h5>
            </div>
            <div class="card-body">
              <section class="landing" id="landingSection">
                  <div class="landing-content">
                      <h1>Hola, Visitor</h1>
                      <p class="subtext">Welcome to DagupanGovLegder ChatBot</p>
                  </div>
              </section>
              <div class="chat-area" id="chatArea" style="display: none;"></div>
              <div class="flex">
                  <input type="text" id="userInput" class="form-control w-full" placeholder="Type your message..." />
                  <button id="sendBtn" class="btn btn-success">Send</button>
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
window.addEventListener("load", function() {
  document.querySelectorAll("textarea").forEach(el => {
    el.style.height = "auto";
    el.style.height = el.scrollHeight + "px";
  });
});
</script>
<script>
  // Pass extracted text to chatbot.js safely
  const pdfContext = <?= json_encode($context) ?>;
</script>
<script src="../../../services/chatbox.js"></script>
<script src="../../../services/chatbox.js"></script>
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
