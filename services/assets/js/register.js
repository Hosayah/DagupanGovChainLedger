document.addEventListener("DOMContentLoaded", () => {
  const govForm = document.getElementById("govForm");
  const auditorForm = document.getElementById("auditorForm");
  const citizenForm = document.getElementById("citizenForm");

  // Government Agency Registration
  govForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const payload = {
      user_type: "govagency",
      name: e.target.agencyName.value,
      email: e.target.email.value,
      password: e.target.password.value,
      contact: e.target.contact.value,
      extra_info: {
        officeCode: e.target.officeCode.value,
        fullName: e.target.fullName.value,
        position: e.target.position.value,
        govId: e.target.govId.value
      }
    };

    await registerUser(payload);
  });

  // Auditor Registration
  auditorForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const payload = {
      user_type: "auditor",
      name: e.target.organization.value,
      email: e.target.email.value,
      password: e.target.password.value,
      contact: e.target.contact.value,
      extra_info: {
        accreditation: e.target.accreditation.value,
        fullName: e.target.fullName.value,
        role: e.target.role.value
      }
    };

    await registerUser(payload);
  });

  // Citizen Registration
  citizenForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const payload = {
      user_type: "citizen",
      name: e.target.fullName.value,
      email: e.target.email.value,
      password: e.target.password.value,
      contact: e.target.contact.value,
      extra_info: {}
    };

    await registerUser(payload);
  });
});

// 🔹 Helper function to send POST request
async function registerUser(payload) {
  try {
    const res = await fetch("http://localhost:3000/auth/register", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include", // ✅ so session cookies can be stored
      body: JSON.stringify(payload),
    });

    const data = await res.json();
    alert(data.msg);

    if (res.ok) {
      // Redirect after successful registration
      window.location.href = "../../../index.html";
    }
  } catch (err) {
    console.error("Registration error:", err);
    alert("Something went wrong. Try again.");
  }
}
