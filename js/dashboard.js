document.addEventListener("DOMContentLoaded", async () => {
    const grid = document.getElementById("grid-container");
    grid.innerHTML = "";
  
    const token = localStorage.getItem("token");
  
    const res = await fetch("/api/modules.php", {
      headers: {
        Authorization: `Bearer ${token}`
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
          <div class="progress-bar ${
            mod.progress === 100 ? "green" : isUnlocked ? "blue" : "grey"
          }">
            <div class="fill" style="width: ${mod.progress}%"></div>
          </div>
          <p class="percent">${mod.progress}% Abgeschlossen</p>
          ${
            isUnlocked
              ? `<button class="action-btn ${
                  mod.progress === 100 ? "ghost" : "primary"
                }" onclick="window.location.href='roadmap.html?modul_id=${mod.id}'">
                   ${mod.progress === 100 ? "Modul ansehen" : "Fortsetzen"}
                 </button>`
              : `<div class="lock-icon">🔒</div>`
          }
        `;
  
        moduleGrid.appendChild(card);
      });

      // Abschlussquiz (quiz.html) als letzter Punkt
      const quizCard = document.createElement("div");
      quizCard.classList.add("grid-item", "quiz-card");
      quizCard.innerHTML = `
        <h3>Abschlussquiz</h3>
        <p class="status">Quiz verfügbar</p>
        <button class="action-btn primary" onclick="window.location.href='quiz.html'">
          Quiz starten
        </button>
      `;
      moduleGrid.appendChild(quizCard);

      // Plattform-Feedback (feedback.html) als letzter Punkt
      const feedbackCard = document.createElement("div");
      feedbackCard.classList.add("grid-item", "feedback-card");
      feedbackCard.innerHTML = `
        <h3>Plattform-Feedback</h3>
        <p class="status">Feedback geben</p>
        <button class="action-btn primary" onclick="window.location.href='feedback.html'">
          Feedback geben
        </button>
      `;
      moduleGrid.appendChild(feedbackCard);
  
      courseWrapper.appendChild(moduleGrid);
      grid.appendChild(courseWrapper);
  
      // Kurs-Ein/Ausklappen
      toggleBtn.addEventListener("click", () => {
        moduleGrid.classList.toggle("hidden");
        toggleBtn.textContent = moduleGrid.classList.contains("hidden") ? "▸" : "▾";
      });
    });
  });