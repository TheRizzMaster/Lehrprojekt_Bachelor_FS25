(async function () {
    const token = localStorage.getItem("token");
    const params = new URLSearchParams(window.location.search);
    const modulId = params.get("modul_id");
  
    const grid = document.getElementById("lesson-grid");
    const titleEl = document.getElementById("module-header");
  
    if (!modulId) {
      alert("Modul-ID fehlt.");
      window.location.href = "/dashboard.html";
      return;
    }
  
    try {
      const res = await fetch(`/api/lessons.php?modul_id=${modulId}`, {
        headers: { Authorization: `Bearer ${token}` }
      });
  
      const data = await res.json();
      if (!res.ok) {
        grid.innerHTML = `<p class="error">Fehler: ${data.error}</p>`;
        return;
      }
  
      // Setze Modul-Titel
      titleEl.textContent = data.module?.title || "Modul";
  
      // Lektionen rendern
      grid.innerHTML = "";
      let unlockNext = true;
  
      data.lessons.forEach((lesson) => {
        const card = document.createElement("div");
        card.classList.add("lesson-card");
  
        if (lesson.completed) {
          card.classList.add("completed");
        } else if (unlockNext) {
          card.classList.add("in-progress");
          unlockNext = false;
        } else {
          card.classList.add("locked");
        }
  
        card.innerHTML = `
          <h3>${lesson.title}</h3>
          <p>${lesson.description}</p>
          <p>Status: ${
            lesson.completed
              ? "✅ Abgeschlossen"
              : card.classList.contains("in-progress")
              ? "🕓 Offen"
              : "🔒 Gesperrt"
          }</p>
          ${
            card.classList.contains("locked") ? `<div class="lock-icon">🔒</div>` : ""
          }
        `;
  
        grid.appendChild(card);
      });
  
    } catch (err) {
      console.error("Fehler beim Laden der Lektionen:", err);
      grid.innerHTML = `<p class="error">Fehler beim Laden der Daten.</p>`;
    }
  })();