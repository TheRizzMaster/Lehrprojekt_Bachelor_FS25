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
        // Token ist gültig
        const data = await res.json();
        return data;
      } else {
        // Token ungültig oder abgelaufen
        localStorage.removeItem("token");
        if (redirectIfInvalid) window.location.href = "/login.html";
        return false;
      }
    } catch (err) {
      console.error("Fehler bei der Sessionprüfung:", err);
      if (redirectIfInvalid) window.location.href = "/login.html";
      return false;
    }
}

checkSession();