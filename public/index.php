<?php
session_start();

// If user is already logged in, redirect based on account type
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    $type = $user['account_type'];

    switch ($type) {
        case 'agency':
            header("Location: ./dist/govagency/dashboard.php");
            exit;
        case 'auditor':
            header("Location: ./dist/auditor/dashboard.php");
            exit;
        case 'citizen':
            header("Location: ./dist/citizen/dashboard.php");
            exit;
        case 'admin':
            header("Location: ./dist/admin/dashboard.php");
            exit;
        default:
            // fallback if session has unknown type
            header("Location: ./dist/auth/login.php");
            exit;
    }
}
?>

<!doctype html>
<html lang="en" data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" dir="ltr" data-pc-theme="light">
  <head>
    <title>DagupanGovChain - Transparency Portal</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="Dagupan Government Transparency Portal" />
    <meta name="keywords" content="transparency, dagupan, government, blockchain, audit" />
    <meta name="author" content="Sniper 2025" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="./dist/assets/fonts/phosphor/duotone/style.css" />
    <link rel="stylesheet" href="./dist/assets/fonts/tabler-icons.min.css" />
    <link rel="stylesheet" href="./dist/assets/fonts/feather.css" />
    <link rel="stylesheet" href="./dist/assets/fonts/fontawesome.css" />
    <link rel="stylesheet" href="./dist/assets/fonts/material.css" />
    <link rel="stylesheet" href="./dist/assets/css/style.css" id="main-style-link" />
     <link href="./src/output.css" rel="stylesheet">
  </head>

  <body class="bg-gray-50 text-gray-800">

  <!-- ✅ Navbar -->
  <nav class="bg-white shadow-md fixed top-0 left-0 right-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
      <div class="text-2xl font-bold text-green-700">
        <span class="text-gray-800">Dagupan</span>GovChain
      </div>
    </div>
  </nav>

  <!-- ✅ Hero Section -->
  <section class="mt-24 text-center px-6 py-20 bg-gradient-to-b from-green-50 to-white">
    <h1 class="text-4xl md:text-5xl font-bold text-green-700 mb-4">Empowering Transparency in Public Spending</h1>
    <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
      View, audit, and understand how government funds are used. Together, let’s promote accountability and transparency in Dagupan City.
    </p>
    <div class="space-x-4">
      <a href="./dist/auth/login.php" class="bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700">Log In</a>
      <a href="./dist/auth/register.php" class="border border-green-600 text-green-600 px-6 py-3 rounded-md hover:bg-green-100">Register</a>
    </div>
  </section>

  <!-- ✅ Transparency Overview -->
  <section id="reports" class="max-w-6xl mx-auto py-16 px-6">
    <h2 class="text-3xl font-bold text-center mb-10 text-gray-800">Transparency Dashboard Preview</h2>
    <div class="grid md:grid-cols-3 gap-8 text-center">
      <div class="bg-white shadow-md p-6 rounded-xl">
        <h3 class="text-xl font-semibold mb-2">₱2.3B</h3>
        <p class="text-gray-500">Total Reported Budget</p>
      </div>
      <div class="bg-white shadow-md p-6 rounded-xl">
        <h3 class="text-xl font-semibold mb-2">142</h3>
        <p class="text-gray-500">Active Government Projects</p>
      </div>
      <div class="bg-white shadow-md p-6 rounded-xl">
        <h3 class="text-xl font-semibold mb-2">58</h3>
        <p class="text-gray-500">Audited Agencies</p>
      </div>
    </div>
  </section>

  <!-- ✅ Citizen Feedback Section -->
  <!--<section id="feedback" class="bg-green-50 py-16">
    <div class="max-w-6xl mx-auto text-center px-6">
      <h2 class="text-3xl font-bold mb-6 text-gray-800">Citizen Feedback</h2>
      <p class="text-gray-600 mb-8">Share your thoughts about local government transparency and projects.</p>
      
      <form class="max-w-xl mx-auto bg-white shadow-md rounded-xl p-6 text-left">
        <label class="block mb-2 font-medium">Your Name</label>
        <input type="text" placeholder="Optional" class="w-full border border-gray-300 rounded-md p-2 mb-4 focus:outline-none focus:ring-2 focus:ring-green-400">

        <label class="block mb-2 font-medium">Comment</label>
        <textarea placeholder="Enter your feedback here..." class="w-full border border-gray-300 rounded-md p-2 h-24 mb-4 focus:outline-none focus:ring-2 focus:ring-green-400"></textarea>

        <button type="submit" class="bg-green-600 text-white px-5 py-2 rounded-md hover:bg-green-700">Submit Feedback</button>
      </form>
    </div>
  </section> -->

  <!-- ✅ Footer -->
  <footer class="bg-gray-900 text-gray-300 py-6 mt-10">
    <div class="max-w-6xl mx-auto text-center">
      <p>© 2025 Dagupan Government Spending Ledger | Transparency Portal</p>
      <p class="text-sm text-gray-500 mt-1">Developed for public transparency and accountability</p>
    </div>
  </footer>

  <script>
      layout_change('false');
      layout_theme_sidebar_change('dark');
      change_box_container('false');
      layout_caption_change('true');
      layout_rtl_change('false');
      preset_change('preset-1');
      main_layout_change('vertical');
    </script>

</body>
</html>
