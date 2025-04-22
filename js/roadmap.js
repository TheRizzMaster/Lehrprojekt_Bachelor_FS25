(async function () {
    const token = localStorage.getItem("token");
    const params = new URLSearchParams(window.location.search);
    const modulId = params.get("modul_id");
  
    if (!modulId) {
    //   alert("Modul-ID fehlt.");
      window.location.href = "/dashboard.html";
    }
  
    const res = await fetch(`/api/lessons.php?modul_id=${modulId}`, {
      headers: { Authorization: `Bearer ${token}` }
    });
  
    const data = await res.json();
    const grid = document.getElementById("lesson-grid");
  
    if (!res.ok) {
      grid.innerHTML = `<p>Fehler: ${data.error}</p>`;
      return;
    }
  
    data.forEach((lesson) => {
      const div = document.createElement("div");
      div.classList.add("lesson-card");
      if (lesson.completed) div.classList.add("completed");
  
      div.innerHTML = `
        <h3>${lesson.title}</h3>
        <p>${lesson.description}</p>
        <p>Status: ${lesson.completed ? "✅ Abgeschlossen" : "🕓 Offen"}</p>
      `;
  
      grid.appendChild(div);
    });
  })();