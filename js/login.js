// Tabs zwischen Login & Registrierung umschalten
function showForm(id) {
  // Formulare umschalten
  document.getElementById("login-form").classList.remove("active");
  document.getElementById("register-form").classList.remove("active");
  document.getElementById(id).classList.add("active");

  // Tab-Schaltflächen aktualisieren
  const buttons = document.querySelectorAll(".tabs button");
  buttons.forEach((btn) => btn.classList.remove("active"));

  if (id === "login-form") {
    buttons[0].classList.add("active");
  } else {
    buttons[1].classList.add("active");
  }
}

// Beim Seitenladen automatisch richtiges Formular anzeigen
document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  if (params.has("register")) {
    showForm("register-form");
  } else {
    showForm("login-form");
  }

  // Login & Register-Formulare einhängen
  const loginForm = document.getElementById("loginForm");
  const registerForm = document.getElementById("registerForm");

  // Login-Submit
  loginForm?.addEventListener("submit", async (e) => {
    e.preventDefault();

    const email = document.getElementById("log_email").value.trim();
    const password = document.getElementById("log_password").value;
    const honeypot = loginForm.querySelector("[name='confirm_email']").value;

    if (honeypot) return; // Spambot

    if (!email || !password) {
      alert("Bitte E-Mail und Passwort eingeben");
      return;
    }

    try {
      const res = await fetch("/auth/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password }),
      });

      const data = await res.json();

      if (res.ok) {
        localStorage.setItem("token", data.token);
        alert("Erfolgreich angemeldet!");
        window.location.href = "/dashboard.html";
      } else {
        alert(data.error || "Fehler beim Anmelden");
      }
    } catch (err) {
      console.error(err);
      alert("Serverfehler beim Login");
    }
  });

  // Registrierung-Submit
  registerForm?.addEventListener("submit", async (e) => {
    e.preventDefault();

    const name = document.getElementById("new-username").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("new-password").value;
    const honeypot = registerForm.querySelector("[name='confirm_email']").value;

    if (honeypot) return; // Spambot

    if (!name || !email || !password) {
      alert("Bitte alle Felder ausfüllen");
      return;
    }

    try {
      const res = await fetch("/auth/register.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, email, password }),
      });

      const data = await res.json();

      if (res.ok) {
        alert("Account erstellt! Bitte bestätige deine E-Mail.");
        registerForm.reset();
        showForm("login-form");
      } else {
        alert(data.error || "Fehler bei der Registrierung");
      }
    } catch (err) {
      console.error(err);
      alert("Serverfehler bei der Registrierung");
    }
  });
});