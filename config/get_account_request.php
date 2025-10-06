<?php
include("config.php");

// Fetch pending users
$query = "
      SELECT 
          u.user_id, 
          u.full_name, 
          u.email, 
          u.account_type,
          u.role, 
          u.status,
          u.created_at,
          COALESCE(a.agency_name, au.organization_name) AS organization,
          COALESCE(a.office_code, au.office_code) AS officeCode,
          COALESCE(a.gov_id_number, au.accreditation_number) AS identifier
      FROM users u
      LEFT JOIN agencies a ON u.user_id = a.user_id
      LEFT JOIN auditors au ON u.user_id = au.user_id
      WHERE u.status = 'pending'
      ORDER BY u.created_at DESC
      LIMIT 5
  ";
  $limit = 0;
  $result = $conn->query($query);
?>
