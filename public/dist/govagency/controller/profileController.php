<?php
require_once("../../../config/config.php");
require_once("../../../DAO/UserDao.php");
if (!isset($_POST['action'])) {
    $_SESSION['isEdit'] = false;
}
$dao = new UserDAO($conn);
$userId = $_SESSION["user"]["id"];
$user = $dao->getUserById($userId); 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
      if (isset($_POST['action'])) {
          if ($_POST['action'] === 'edit') {
              $_SESSION['isEdit'] = !$_SESSION['isEdit'];
          } elseif ($_POST['action'] === 'save') {
            // Save changes
            $name = trim($_POST["name"]);
            $email = trim($_POST["email"]);
            $contact = trim($_POST["contact"]);
            $result = $dao->updateUserById( $name, $email, $contact, $userId);
            if($result){
                $_SESSION['isEdit'] = false;
                echo "<script>alert('✅ Account details saved.')</script>";
            }
          } elseif ($_POST['action'] === 'change') {
            // Change Password Logic
            $oldPassword = trim(preg_replace('/\s+/', ' ', $_POST['oldPassword']));
            $newPassword = trim(preg_replace('/\s+/', ' ', $_POST['newPassword']));
            $confirm = trim(preg_replace('/\s+/', ' ', $_POST['confirm']));

            //$old = password_hash($oldPassword, PASSWORD_BCRYPT);
            //echo $old . '<br>';
            //echo $user['password_hash'] . '<br>';
            // Check old pass
            
            if(!password_verify($oldPassword, $user['password_hash'])){
                echo "<script>alert('❌ Old password is wrong.')</script>";
            } else if (strlen($newPassword) < 6 || preg_match( '/\s+/', $newPassword)) {
                echo "<script>alert('❌ New password must be atleast 6 characters and not have whitespaces')</script>";
            } else if ($newPassword === $oldPassword) {
                echo "<script>alert('❌ New password must not be the same as the old password')</script>";
            } else if ($newPassword != $confirm){
                echo "<script>alert('❌ New password and confirm password must match!')</script>";
            } else {
                $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                $result = $dao->updatePasswordById($hash, $userId);
                if($result){
                     echo "<script>alert('✅ Password changed successfully')</script>";
                } else {
                    echo "<script>alert('✅ ❌ Something went wrong, please try again later.')</script>";
                }
               // $result->close();
            }
          }
      }
  }