// assets/js/component-loader.js
import { setupLogout } from "./logout.js";

export async function loadSidebar(containerId) {
  try {
    // 1. Ask backend who the user is
    const res = await fetch("http://localhost:3000/auth/me", {
      credentials: "include",
    });

    if (!res.ok) throw new Error("Not authenticated");
    const { account_type } = await res.json();
    console.log(account_type);

    // 2. Pick sidebar path based on role
    const sidebarPath = {
      citizen: "../../components/citizen-sidebar.html",
      agency: "../../components/gov-sidebar.html",
      auditor: "../../components/auditor-sidebar.html",
      admin: "../../components/admin-sidebar.html",
    }[account_type];

    if (!sidebarPath) throw new Error(`No sidebar for role: ${account_type}`);

    // 3. Load sidebar
    const sidebarHtml = await fetch(sidebarPath).then((r) => r.text());
    document.getElementById(containerId).innerHTML = sidebarHtml;

    // 4. Activate logout
    setupLogout();
  } catch (err) {
    console.error("Sidebar load error:", err);
    // fallback: redirect to login if unauthorized
    window.location.href = "../../../index.html";k
  }
}
