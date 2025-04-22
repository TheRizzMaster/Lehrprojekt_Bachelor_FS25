checkSession();

async function checkSession(redirectIfInvalid = true) {
  const token = localStorage.getItem("token");

  if (!token) {
    if (redirectIfInvalid) window.location.href = "/login.html";
    return false;
  }

  try {
    const res = await fetch("/auth/verify.php", {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });

    if (res.ok) {
      return true; // Token gültig
    } else {
      localStorage.removeItem("token");
      if (redirectIfInvalid) window.location.href = "/login.html";
      return false;
    }
  } catch (err) {
    console.error("Sessionprüfung fehlgeschlagen:", err);
    if (redirectIfInvalid) window.location.href = "/login.html";
    return false;
  }
}


function logout() {
  localStorage.removeItem("token");
  window.location.href = "/login.html";
}