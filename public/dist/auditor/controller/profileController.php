<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
      if (isset($_POST['action'])) {
          if ($_POST['action'] === 'edit') {
              $_SESSION['isEdit'] = !$_SESSION['isEdit'];
          } elseif ($_POST['action'] === 'save') {
              $_SESSION['limit'] -= 5;
          } 
      }
  }