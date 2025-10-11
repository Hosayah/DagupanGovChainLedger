document.addEventListener("DOMContentLoaded", async () => {
    try {
      const res = await fetch("http://localhost:3000/auth/check", {
        method: "GET",
        credentials: "include"
      });
      const data = await res.json();

      if (data.loggedIn) {
        // Redirect based on account type
        const type = data.user.account_type;
        if (type === "agency") {
          window.location.href = "./src/pages/Agency/dashboard.html";
        } else if (type === "auditor") {
          window.location.href = "./src/pages/Auditor/dashboard.html";
        } else if (type === "citizen") {
          window.location.href = "./src/pages/Citizen/dashboard.html";
        } else if (type === "admin") {
          window.location.href = "./src/pages/Admin/dashboard.html";
        }
      }
      // else: stay on index.html
    } catch (err) {
      console.error("Session check error:", err);
    }
  });