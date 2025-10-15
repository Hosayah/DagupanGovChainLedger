<?php 
include("../../../config/config.php");
include("../../../DAO/ProjectDao.php");
include("../../../DAO/RecordDao.php");


if (isset($_GET['id'])) {
    $project_id = $_GET['id'];
    $project = $projectDao->getProjectById($project_id);
}

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
        $stmt = $conn->prepare("
          UPDATE projects SET title= ?, category = ?, description = ? WHERE project_id = ?;
        ");
        $stmt->bind_param("sssi", $title, $category, $description, $project_id);
        if ($stmt->execute()) {
            header("Location: edit-project.php?id=$project_id&action=edit&updated=1");
            exit;
        } else {
            echo "<script>alert('❌ Error inserting project: {$stmt->error}');</script>";
        }
    }
}
