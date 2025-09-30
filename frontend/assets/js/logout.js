// assets/js/logout.js
export function setupLogout() {
  const logoutBtn = document.getElementById("logoutBtn");
  if (!logoutBtn) return; // no button on this page

  logoutBtn.addEventListener("click", async () => {
    try {
      const response = await fetch("http://localhost:3000/auth/logout", {
        method: "POST",
        credentials: "include",
      });

      if (!response.ok) throw new Error("Logout failed");

      const data = await response.json();
      console.log(data.message);

      // Redirect after logout
      window.location.href = "/index.html";
    } catch (err) {
      console.error("Logout error:", err);
    }
  });
}
