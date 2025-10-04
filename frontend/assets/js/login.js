// assets/js/login.js
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("loginForm");
  const msg = document.getElementById("msg");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = {
      email: form.email.value.trim(),
      password: form.password.value.trim(),
    };

    try {
      const response = await fetch("http://localhost:3000/auth/login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "include",
        body: JSON.stringify(formData),
      });

      const data = await response.json();

      if (response.ok) {
        msg.textContent = "✅ Login successful! Redirecting...";
        msg.classList.remove("text-red-600");
        msg.classList.add("text-green-600");

        // Store token if backend sends it
        if (data.token) {
          //localStorage.setItem("token", data.token);
        }

        // Save role & account_type
        if (data.user) {
          localStorage.setItem("role", data.user.role);
          localStorage.setItem("account_type", data.user.account_type);
        }

        // Redirect based on account_type (not role)
        setTimeout(() => {
          if (data.user.account_type === "agency") {
            window.location.href = "./src/pages/GovAgency/dashboard.html";
          } else if (data.user.account_type === "auditor") {
            window.location.href = "./src/pages/Auditor/dashboard.html";
          } else if (data.user.account_type === "admin"){
            window.location.href = "./src/pages/Admin/dashboard.html"
          } else {
            window.location.href = "../Citizen/dashboard.html";
          }
        }, 1500);
      } else {
        msg.textContent = `❌ ${data.message || "Invalid email or password"}`;
        msg.classList.remove("text-green-600");
        msg.classList.add("text-red-600");
      }
    } catch (error) {
      msg.textContent = "⚠️ Server error. Please try again later.";
      msg.classList.remove("text-green-600");
      msg.classList.add("text-red-600");
      console.error("Login error:", error);
    }
  });
});
