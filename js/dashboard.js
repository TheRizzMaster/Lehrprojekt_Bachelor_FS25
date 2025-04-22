document.addEventListener("DOMContentLoaded", async () => {
    const grid = document.getElementById("grid-container");
    grid.innerHTML = ""; // vorher leeren
  
    const token = localStorage.getItem("token");
  
    const res = await fetch("/api/modules.php", {
      headers: {
        Authorization: `Bearer ${token}`,
        "Content-Type:": "application/json"
      }
    });
  
    const data = await res.json();
  
    if (!res.ok) {
      grid.innerHTML = "<p>Fehler beim Laden der Module</p>";
      return;
    }
  
    data.forEach((course, courseIndex) => {
      // Kurscontainer
      const courseWrapper = document.createElement("div");
      courseWrapper.classList.add("course-wrapper");
  
      // Kurskopf (ein/ausklappbar)
      const courseHeader = document.createElement("div");
      courseHeader.classList.add("course-header");
      courseHeader.innerHTML = `
        <h2>${course.title}</h2>
        <p>${course.description || ""}</p>
      `;
  
      const toggleBtn = document.createElement("button");
      toggleBtn.textContent = "▾";
      toggleBtn.classList.add("toggle-btn");
  
      courseHeader.appendChild(toggleBtn);
      courseWrapper.appendChild(courseHeader);
  
      // Modul-Grid
      const moduleGrid = document.createElement("div");
      moduleGrid.classList.add("module-grid");
  
      let unlockNext = true;
  
      course.modules.forEach((mod, index) => {
        const card = document.createElement("div");
        card.classList.add("grid-item");
  
        const isUnlocked = mod.progress === 100 || unlockNext;
        const isFirst = index === 0;
  
        if (mod.progress === 100) {
          card.classList.add("done");
        } else if (isUnlocked) {
          card.classList.add("in-progress");
          unlockNext = false;
        } else {
          card.classList.add("locked");
        }
  
        card.innerHTML = `
          <h3>${mod.title}</h3>
          <p class="status">
            ${
              mod.progress === 100
                ? "Fertig"
                : isUnlocked
                ? "In Bearbeitung"
                : "Schliesse das vorherige Modul zuerst ab"
            }
          </p>
          <div class="progress-bar ${mod.progress === 100 ? "green" : isUnlocked ? "blue" : "grey"}">
            <div class="fill" style="width: ${mod.progress}%"></div>
          </div>
          <p class="percent">${mod.progress}% Abgeschlossen</p>
          ${
            isUnlocked
              ? `<button class="action-btn ${mod.progress === 100 ? "ghost" : "primary"}">
                   ${mod.progress === 100 ? "Modul ansehen" : "Fortsetzen"}
                 </button>`
              : `<div class="lock-icon">🔒</div>`
          }
        `;
  
        moduleGrid.appendChild(card);
      });
  
      courseWrapper.appendChild(moduleGrid);
      grid.appendChild(courseWrapper);
  
      // Kurs-Ein/Ausklappen
      toggleBtn.addEventListener("click", () => {
        moduleGrid.classList.toggle("hidden");
        toggleBtn.textContent = moduleGrid.classList.contains("hidden") ? "▸" : "▾";
      });
    });
  });