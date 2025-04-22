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

  const loginForm = document.getElementById("loginForm");
  const registerForm = document.getElementById("registerForm");
  const loginMessage = document.getElementById("log_error");
  const registerMessage = document.getElementById("reg_error");

  function showMessage(element, message, isSuccess = false) {
    element.textContent = message;
    element.style.display = "block";
    element.style.color = isSuccess ? "green" : "red";
  }

  // Login
  loginForm?.addEventListener("submit", async (e) => {
    e.preventDefault();
    loginMessage.style.display = "none";

    const email = document.getElementById("log_email").value.trim();
    const password = document.getElementById("log_password").value;
    const honeypot = loginForm.querySelector("[name='confirm_email']").value;

    if (honeypot) return;

    if (!email || !password) {
      showMessage(loginMessage, "Bitte E-Mail und Passwort eingeben");
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
        showMessage(loginMessage, "Erfolgreich angemeldet!", true);
        // 0.5 sekunden warten, bevor weitergeleitet wird
        setTimeout(() => {
          window.location.href = "/dashboard.html";
        }, 500);
      } else {
        showMessage(loginMessage, data.error || "Fehler beim Anmelden");
      }
    } catch (err) {
      console.error(err);
      showMessage(loginMessage, "Serverfehler beim Login");
    }
  });

  // Registrierung
  registerForm?.addEventListener("submit", async (e) => {
    e.preventDefault();
    registerMessage.style.display = "none";

    const name = document.getElementById("new-username").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("new-password").value;
    const honeypot = registerForm.querySelector("[name='confirm_email']").value;

    if (honeypot) return;

    if (!name || !email || !password) {
      showMessage(registerMessage, "Bitte alle Felder ausfüllen");
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
        showMessage(registerMessage, "Account erstellt! Bitte E-Mail bestätigen.", true);
        registerForm.reset();
        showForm("login-form");
      } else {
        showMessage(registerMessage, data.error || "Fehler bei der Registrierung");
      }
    } catch (err) {
      console.error(err);
      showMessage(registerMessage, "Serverfehler bei der Registrierung");
    }
  });
});