 <?php 
 if (!isset($_SESSION['user'])) {
    header("Location: ../../auth/login.php");
    exit;
  } 
  $name = $_SESSION["user"]["name"];

?>
 <!-- [ Sidebar Menu ] start -->
 <nav class="pc-sidebar">
   <div class="navbar-wrapper">
    <br>
     <div class="m-header flex items-center justify-center py-4 px-6 h-header-height mb-2">
       <a href="../admin/dashboard.php" class="b-brand flex items-center justify-center">
         <img src="../assets/images/logo/logo2.jpg" alt="logo here" class="w-30" /> <!-- logo images here -->
       </a>
     </div>
     <div class="navbar-content h-[calc(100vh_-_74px)] py-2.5">
       <div class="shrink-0 flex items-center justify-left mb-5">&nbsp;&nbsp;&nbsp;&nbsp;
         <h5 class="text-left font-medium text-[15px] flex items-center gap-2">
          <?= htmlspecialchars($name) ?>
         </h5>
       </div>
       <div class="grow ms-3 text-center mb-4">
       </div>
       <ul class="pc-navbar">
         <li class="pc-item pc-caption">
           <label>Navigation</label>
         </li>
         <li class="pc-item"> <!-- Dashboard menu -->
           <a href="../admin/dashboard.php" class="pc-link">
             <span class="pc-micon"><i data-feather="home"></i></span>
             <span class="pc-mtext">Dashboard</span>
           </a>
         </li>

         <li class="pc-item"> <!-- Menu 01 -->
           <a href="../admin/account-approval.php" class="pc-link">
             <span class="pc-micon"><i data-feather="user"></i></span>
             <span class="pc-mtext">Account approval</span>
           </a>
         </li>

         <li class="pc-item pc-hasmenu"> <!-- Menu 02 -->
           <a href="../admin/add-admin.php" class="pc-link">
             <span class="pc-micon"> <i data-feather="user-plus"></i></span>
             <span class="pc-mtext">Add New Admin</span>
           </a>
         </li>

         <!-- Menu with sub menu -->
         <li class="pc-item pc-hasmenu">
           <a href="#!" class="pc-link"><span class="pc-micon"> <i data-feather="user-minus"></i> </span><span
               class="pc-mtext">Manage Accounts</span><span class="pc-arrow"><i class="ti ti-chevron-right"></i></span></a>
           <ul class="pc-submenu">
             <li class="pc-item"><a class="pc-link" href="../admin/approved-accounts.php">Active Accounts</a></li>
             <li class="pc-item"><a class="pc-link" href="../admin/rejected-accounts.php">Rejected Accounts</a></li>
             <li class="pc-item"><a class="pc-link" href="../admin/suspended-accounts.php">Suspended Accounts</a></li>
           </ul>
         </li>
         <!-- Menu with submenu end -->

         <!-- Settings -->
         <li class="pc-item pc-caption">
           <label>Settings</label><i data-feather="wrench"></i>
         </li>
         <li class="pc-item pc-hasmenu">
           <!--<a href="logout.php" class="pc-link" onclick="return confirm('Do you really want to Log-Out?')"> -->
           <a href="../auth/logout.php" class="pc-link">
             <span class="pc-micon"> <i data-feather="log-out"></i></span><span class="pc-mtext">Log-Out</span>
           </a>
         </li>

       </ul>
     </div>
   </div>
 </nav>
 <!-- [ Sidebar Menu ] end -->