<?php
session_start();
include("../config/config.php");
include("../DAO/RecordDao.php");
include("../DAO/ProjectDao.php");
include("../DAO/AuditDao.php");
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
$recordDao = new RecordDAO($conn);
//$projectDao - new ProjectDAO($conn);
$auditDao = new AuditDAO($conn);
$records = $recordDao->getRecordCounters(1);
//$projects = $projectDao->getProjectCounters(1);
$audits = $auditDao->getAuditCounters(1);
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
  <style>
    .reveal {
      opacity: 0;
      transform: translateY(24px);
      transition: all .8s ease
    }

    .reveal.show {
      opacity: 1;
      transform: none
    }

    .blockchain-img {
      max-width: 560px;
      width: 100%;
      height: auto
    }

    nav.bg-white.fixed {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 999;
      height: 72px;
      /* nav height */
      background: #ffffff;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
      -webkit-backdrop-filter: blur(4px);
      backdrop-filter: blur(4px);
    }

    /* Inner container (overrides Tailwind-like inner classes safely) */
    nav.bg-white.fixed>div {
      max-width: 1120px;
      margin: 0 auto;
      padding: 0 18px;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-sizing: border-box;
    }

    /* Logo sizing (keeps your image path and inline width if present) */
    nav.bg-white.fixed img {
      height: 48px;
      /* adjust to taste */
      width: auto;
      display: block;
      object-fit: contain;
    }

    /* Auth action area (expected to be right-side links/buttons) */
    /* If you don't have a container, wrap your login/register anchors in a div.nav-actions */
    .nav-actions {
      display: flex;
      gap: 12px;
      align-items: center;
    }

    /* Generic button link reset */
    .nav-actions a,
    .nav-actions .btn {
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 10px 16px;
      border-radius: 8px;
      font-weight: 700;
      font-size: 14px;
      line-height: 1;
      cursor: pointer;
      box-sizing: border-box;
      transition: background .12s ease, color .12s ease, transform .06s ease;
      border: 1px solid transparent;
    }

    /* Primary (Log In) */
    .btn-login,
    .nav-actions a.login {
      background: #16a34a;
      /* green-600 */
      color: #fff;
      border-color: rgba(0, 0, 0, 0.03);
    }

    .btn-login:hover,
    .nav-actions a.login:hover {
      background: #15803d;
      transform: translateY(-1px);
    }

    /* Secondary (Register) */
    .btn-register,
    .nav-actions a.register {
      background: transparent;
      color: #16a34a;
      border-color: #16a34a;
    }

    .btn-register:hover,
    .nav-actions a.register:hover {
      background: rgba(22, 163, 74, 0.06);
      transform: translateY(-1px);
    }

    /* Accessibility focus */
    .nav-actions a:focus,
    .nav-actions .btn:focus {
      outline: 3px solid rgba(22, 163, 74, 0.18);
      outline-offset: 2px;
    }

    .blockchain-card {
      width: 25rem;
      height: auto;
    }

    /* Small-screen adjustments (still show buttons — no hamburger) */
    @media (max-width: 720px) {
      nav.bg-white.fixed {
        height: 64px;
      }

      nav.bg-white.fixed>div {
        padding: 0 12px;
      }

      nav.bg-white.fixed img {
        height: 42px;
      }

      .nav-actions a,
      .nav-actions .btn {
        padding: 8px 12px;
        font-size: 13px;
        border-radius: 6px;
      }
    }
  </style>
</head>

<body class="bg-gray-50 text-gray-800">
  
  <!-- ✅ Navbar -->
  <nav class="bg-white shadow-md fixed top-0 left-0 right-0 z-50 flex justify-evenly">
    <div class="">
      <a href="index.php"><img src="../public/dist/assets/images/logo/logo2.jpg" alt="" class="w-xl h-auto" style="width: 10rem; "></a>
    </div>
    <div class="flex gap-4">
      <a href="./dist/auth/login.php" class="bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700">Log In</a>
      <a href="./dist/auth/register.php" class="border border-green-600 text-green-600 px-6 py-3 rounded-md hover:bg-green-100">Register</a>
    </div>
  </nav>
  <!-- ../public/dist/assets/images/logo/logo2.png ------------------------------------------------------------------>
  <!-- ✅ Hero Section -->
  <section class="mt-2 px-6 py-20 bg-gradient-to-b from-green-50 to-white">
    <div class="max-w-7xl mx-auto flex gap-10 items-center">
      <div class="flex-1 flex justify-center">
        <img src="../public/dist/assets/images/landing-page/hero.png" alt="Dagupan hero" class="w-full max-w-[560px] h-auto" loading="eager" />
      </div>
      <div class="flex-1 text-left">
        <h1 class="text-4xl md:text-5xl font-bolder text-green-700 mb-4 " style="font-weight: 900;">Empowering Transparency in Public Spending</h1>
        <p class="text-lg text-gray-600 mb-8 max-w-2xl">
          View, audit, and understand how government funds are used. Together, let’s promote accountability and transparency in Dagupan City.
        </p>
        <div class="flex gap-4">
          <a href="./dist/auth/login.php" class="bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700">Log In</a>
          <a href="./dist/auth/register.php" class="border border-green-600 text-green-600 px-6 py-3 rounded-md hover:bg-green-100">Register</a>
        </div>
      </div>
    </div>
  </section>
  <!-- ✅ Transparency Overview -->
  <section id="reports" class="max-w-6xl mx-auto py-16 px-6 reveal" data-reveal>
    <h2 class="text-4xl font-bolder text-center text-gray-800 mb-6" style="font-weight: 900;">Transparency Dashboard Preview</h2>
    <div class="grid md:grid-cols-3 gap-8">
      <div class="bg-white shadow-md p-6 rounded-xl opacity-0 translate-y-6 transition-all duration-700" data-reveal>
        <div class="flex items-start gap-4">
          <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 flex-shrink-0">
            <!-- budget icon -->
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <rect x="2.5" y="6" width="19" height="12" rx="2" stroke="#16a34a" stroke-width="2" />
              <path d="M7 12h10" stroke="#10b981" stroke-width="2" stroke-linecap="round" />
              <circle cx="12" cy="12" r="2.25" stroke="#059669" stroke-width="2" />
            </svg>
          </span>
          <div class="text-left">
            <h3 class="text-2xl font-semibold leading-tight">
              <span class="countup" data-target="<?= htmlspecialchars((float)$records['sum']) ?>" data-decimals="2" data-prefix="₱ " data-step="1000000" data-interval="30">0</span>
            </h3>
            <p class="text-gray-500">Total Reported Budget</p>
          </div>
        </div>
      </div>
      <div class="bg-white shadow-md p-6 rounded-xl opacity-0 translate-y-6 transition-all duration-700" data-reveal>
        <div class="flex items-start gap-4">
          <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 flex-shrink-0">
            <!-- projects icon -->
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3H4V7z" stroke="#16a34a" stroke-width="2" />
              <rect x="4" y="10" width="16" height="7" rx="2" stroke="#10b981" stroke-width="2" />
              <path d="M8 7h8" stroke="#16a34a" stroke-width="2" stroke-linecap="round" />
            </svg>
          </span>
          <div class="text-left">
            <h3 class="text-2xl font-semibold leading-tight"><?= htmlspecialchars($records['total']) ?></h3>
            <p class="text-gray-500">Total Government Projects</p>
          </div>
        </div>
      </div>
      <div class="bg-white shadow-md p-6 rounded-xl opacity-0 translate-y-6 transition-all duration-700" data-reveal>
        <div class="flex items-start gap-4">
          <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 flex-shrink-0">
            <!-- audits icon -->
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <rect x="5" y="3" width="14" height="18" rx="2" stroke="#16a34a" stroke-width="2" />
              <path d="M8 8h8M8 12h8M8 16h6" stroke="#10b981" stroke-width="2" stroke-linecap="round" />
            </svg>
          </span>
          <div class="text-left">
            <h3 class="text-5xl font-semibold leading-tight"><?= htmlspecialchars($audits['total']) ?></h3>
            <p class="text-gray-500 text-">Total Submitted Audits</p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <section id="about" style="padding: 4rem;" class="max-w-full mx-auto py-1 px-6  rounded-xl shadow-sm mt-8 reveal bg-white rounded-xl p-6" data-reveal>
    <div class="flex flex-col md:flex-row items-center gap-6 max-w-6xl mx-auto">
      <!-- Logo -->
      <div class=" flex-shrink-0" style="width: 25rem;">

        <img src="../public/dist/assets/images/logo/logo3.png" alt="DagupanGovLedger logo" class="w-full h-auto object-contain" />
      </div>

      <!-- tung tung tung — sahur, ta ta ta — sahur  
tung tung tung — plates and laughter, ta ta ta — sahur  
tung tung tung — hearts awake, ta ta ta — sahur  
tung tung tung — come together, ta ta ta — sahur -->
      <div class="flex-1">

        <p class="text-gray-600 leading-relaxed text-xl p-5">
          <span style="color:  oklch(62.7% 0.194 149.214); font-weight: 900;">DagupanGovLedger</span> uses blockchain technology to record and verify Dagupan City’s project spending — including budgets, timelines, and audit reports — ensuring every update is transparent, tamper-proof, and publicly verifiable.
        </p>
      </div>
    </div>
  </section>
  <!-- ✅ What is Blockchain (Citizen-friendly) -->
  <section class="max-w-6xl mx-auto py-16 px-6">

    <div class="reveal mb-5" id="blockchain-row">
      <div class="flex items-center gap-10">

        <div class="flex-1 p-6">
          <h2 class="text-3xl font-bold text-gray-800 mb-4">What is <span class="text-green-600">Blockchain</span>?</h2>
          <p class="text-gray-600 leading-relaxed text-xl">
            Blockchain is a secure, shared record book. Information is grouped into blocks, linked in order,
            and protected so changes are obvious to everyone. This makes the data transparent and hard to
            tamper with, helping citizens trust what they see.
          </p>

        </div>
        <div class="flex-1 flex justify-center">
          <img src="../public/dist/assets/images/landing-page/blockchain.png" alt="What is blockchain" class="blockchain-img" loading="lazy" />
        </div>
      </div>
    </div>

    <div class="flex justify-center gap-8 reveal">
      <div class="bg-white shadow-md p-6 rounded-xl opacity-0 translate-y-6 transition-all duration-700 blockchain-card" data-reveal>
        <div class="flex items-start gap-4">
          <div class="text-left">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-green-100 flex-shrink-0">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <!-- Circular progress outline -->
                <circle cx="12" cy="12" r="9" stroke="#16a34a" stroke-width="2" />
                <!-- Speed arrow -->
                <path d="M12 12l4-2" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                <!-- Dash marks to show motion -->
                <path d="M12 3v2M21 12h-2M12 21v-2M3 12h2" stroke />
              </svg>
            </span>
            <h3 class="text-2xl font-semibold leading-tight">
              Efficiency
            </h3>
            <p class="text-gray-500">Faster verification reduces manual checks and delays.</p>
          </div>
        </div>
      </div>
      <div class="bg-white shadow-md p-6 rounded-xl opacity-0 translate-y-6 transition-all duration-700 blockchain-card" data-reveal>
        <div class="flex items-start gap-4">
          <div class="text-left">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-green-100 flex-shrink-0">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M3 12l7-7 11 11-7 7L3 12z" stroke="#16a34a" stroke-width="2" stroke-linejoin="round" />
                <path d="M10 5l9 9" stroke="#10b981" stroke-width="2" />
              </svg>
            </span>
            <h3 class="text-2xl font-semibold leading-tight">
              Transparency
            </h3>
            <p class="text-gray-500">Public can view spending records and verify updates.</p>
          </div>
        </div>
      </div>
      <div class="bg-white shadow-md p-6 rounded-xl opacity-0 translate-y-6 transition-all duration-700 blockchain-card" data-reveal>
        <div class="flex items-start gap-4">
          <div class="text-left">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-green-100 flex-shrink-0">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M12 3l7 4v5c0 4.418-3.582 7-7 9-3.418-2-7-4.582-7-9V7l7-4z" stroke="#16a34a" stroke-width="2" />
                <path d="M9 12l2 2 4-4" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </span>
            <h3 class="text-2xl font-semibold leading-tight">Integrity</h3>
            <p class="text-gray-500">Tamper-evident records discourage manipulation or hidden edits.</p>
          </div>
        </div>
      </div>
      <div class="bg-white shadow-md p-6 rounded-xl opacity-0 translate-y-6 transition-all duration-700 blockchain-card" data-reveal>
        <div class="flex items-start gap-4">

          <div class="text-left">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-green-100 flex-shrink-0">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <rect x="4" y="3" width="16" height="14" rx="2" stroke="#16a34a" stroke-width="2" />
                <path d="M8 7h8M8 11h8" stroke="#10b981" stroke-width="2" stroke-linecap="round" />
                <path d="M8 19h8" stroke="#22c55e" stroke-width="2" stroke-linecap="round" />
              </svg>
            </span>
            <h3 class="text-5xl font-semibold leading-tight">Accountability</h3>
            <p class="text-gray-500 text-">Track project progress and audits with a permanent trail.</p>
          </div>
        </div>
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
    // Count-up animation on intersection
    (function() {
      function formatNumber(value, decimals) {
        const fixed = decimals ? value.toFixed(decimals) : Math.round(value).toString();
        const parts = fixed.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return parts.join('.');
      }

      function animateCount(el) {
        const target = parseFloat(el.getAttribute('data-target') || '0');
        const decimals = parseInt(el.getAttribute('data-decimals') || '0', 10);
        const prefix = el.getAttribute('data-prefix') || '';
        const customStep = parseFloat(el.getAttribute('data-step') || '0');
        const customInterval = parseInt(el.getAttribute('data-interval') || '0', 10);
        if (!isFinite(target) || target <= 0) {
          el.textContent = prefix + (decimals ? Number(0).toFixed(decimals) : '0');
          return;
        }
        const durationMs = 1500; // default total animation time
        const defaultIntervalMs = 250; // default frame interval
        const intervalMs = customInterval > 0 ? customInterval : defaultIntervalMs;
        const isMillionOrMore = target >= 1000000;
        const stepBase = customStep > 0 ? customStep : (isMillionOrMore ? 100000 : Math.max(1, Math.ceil(target / (durationMs / intervalMs))));
        let current = Math.max(1, stepBase);

        const timer = setInterval(function() {
          current += stepBase;
          if (current >= target) {
            current = target;
            clearInterval(timer);
          }
          el.textContent = prefix + formatNumber(current, decimals);
        }, intervalMs);
      }

      const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const el = entry.target;
            animateCount(el);
            obs.unobserve(el);
          }
        });
      }, {
        threshold: 0.4
      });

      document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.countup').forEach(el => observer.observe(el));
      });
    })();
    // Reveal on scroll
    (function() {
      const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.remove('opacity-0');
            entry.target.classList.remove('translate-y-6');
            revealObserver.unobserve(entry.target);
          }
        });
      }, {
        threshold: 0.25
      });

      document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-reveal]').forEach(el => revealObserver.observe(el));
      });
    })();
    // Scroll reveal for .reveal blocks
    (function() {
      const obs = new IntersectionObserver((entries) => {
        entries.forEach(e => {
          if (e.isIntersecting) {
            e.target.classList.add('show');
            obs.unobserve(e.target);
          }
        })
      }, {
        threshold: 0.25
      });
      document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.reveal').forEach(el => obs.observe(el));
      });
    })();
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