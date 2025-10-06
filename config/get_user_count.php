<?php
include("config.php");

// Fetch pending users
$total_users_query = "
    SELECT 
        COUNT(user_id) AS count
    FROM users
";

$totalUsers = 0;
$total_users_count = $conn->query($total_users_query);
if ($total_users_count) {
    $row = $total_users_count->fetch_assoc();
    $totalUsers = $row['count'];
} else {
    echo "Error: " . $conn->error;
}

$approved_users_query = "
    SELECT 
        COUNT(user_id) AS count,
        status
    FROM users
    GROUP BY status
";

$approved_users_count = $conn->query($approved_users_query);
$approved = 0;
$pending = 0;

// Check if query was successful
if ($approved_users_count) {
    while ($row = $approved_users_count->fetch_assoc()) {
        if ($row["status"] == "approved") {
            $approved = $row["count"];
        } else if ($row["status"] == "pending") {
            $pending = $row["count"];
        }
    }
} else {
    echo "Error: " . $conn->error;
}

$account_type_count_query = "
    SELECT 
        COUNT(user_id) AS count,
        account_type
    FROM users
    GROUP BY account_type
";

$account_type_count = $conn->query($account_type_count_query);
$agency = 0;
$auditor = 0;
$citizen = 0;

if ($account_type_count) {
    while ($row = $account_type_count->fetch_assoc()) {
        if ($row["account_type"] == "agency") {
            $agency = $row["count"];
        } else if ($row["account_type"] == "auditor") {
            $auditor = $row["count"];
        } else if ($row["account_type"] == "citizen") {
            $citizen = $row["count"];
        }
    }
}

?>
