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
       <a href="../govagency/dashboard.php" class="b-brand flex items-center justify-center">
         <img src="../assets/images/logo/logo2.jpg" alt="logo here" class="w-30" /> <!-- logo images here -->
       </a>
     </div>
     <div class="navbar-content h-[calc(100vh_-_74px)] py-2.5">
       <div class="shrink-0 flex items-center justify-left mb-5">&nbsp;&nbsp;&nbsp;&nbsp;
         <h5 class="text-left font-medium text-[15px] flex items-center gap-2">
           Hello, <?= htmlspecialchars($name) ?>
         </h5>
       </div>
       <div class="grow ms-3 text-center mb-4">
       </div>
       <ul class="pc-navbar">
         <li class="pc-item pc-caption">
           <label>Navigation</label>
         </li>
         <li class="pc-item"> <!-- Dashboard menu -->
           <a href="../govagency/dashboard.php" class="pc-link">
             <span class="pc-micon"><i data-feather="home"></i></span>
             <span class="pc-mtext">Dashboard</span>
           </a>
         </li>

         <li class="pc-item"> <!-- Menu 01 -->
           <a href="../govagency/view-projects.php" class="pc-link">
             <span class="pc-micon"><i data-feather="file-text"></i></span>
             <span class="pc-mtext">View Projects</span>
           </a>
         </li>

         <li class="pc-item pc-hasmenu"> <!-- Menu 02 -->
           <a href="../govagency/view-records.php" class="pc-link">
             <span class="pc-micon"><i class="ph ph-receipt"></i></span>
             <span class="pc-mtext">View Records</span>
           </a>
         </li>
         <li class="pc-item pc-hasmenu"> <!-- Menu 02 -->
           <a href="../govagency/add-project.php" class="pc-link">
             <span class="pc-micon"> <i data-feather="plus-square"></i></span>
             <span class="pc-mtext">Add Project & Record</span>
           </a>
         </li>

       

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