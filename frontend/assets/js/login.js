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
      const response = await fetch("http://localhost:3000/api/auth/login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData),
      });

      const data = await response.json();

      if (response.ok) {
        msg.textContent = "✅ Login successful! Redirecting...";
        msg.classList.remove("text-red-600");
        msg.classList.add("text-green-600");

        // Store token if backend sends it
        if (data.token) {
          localStorage.setItem("token", data.token);
          localStorage.setItem("role", data.role); // govagency, auditor, or citizen
        }

        // Redirect based on role
        setTimeout(() => {
          if (data.role === "govagency") {
            window.location.href = "./src/pages/GovAgency/dashboard.html";
          } else if (data.role === "auditor") {
            window.location.href = "./src/pages/Auditor/dashboard.html";
          } else {
            window.location.href = "./src/pages/Citizen/dashboard.html";
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
