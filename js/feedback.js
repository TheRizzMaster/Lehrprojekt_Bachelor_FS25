
const sliders = document.querySelectorAll('input[type="range"]');

sliders.forEach(slider => {
function updateBackground(el) {
    const val = (el.value - el.min) / (el.max - el.min) * 100;
    el.style.background = `linear-gradient(to right, #3182ce 0%, #3182ce ${val}%, #e2e8f0 ${val}%, #e2e8f0 100%)`;
}

updateBackground(slider);

slider.addEventListener('input', () => {
    updateBackground(slider);
});
});

document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");
    const params = new URLSearchParams(window.location.search);
    const moduleId = params.get("module_id");
    const lessonId = params.get("lesson_id");
    const token = localStorage.getItem("token");
  
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
  
      const data = {
        module_id: moduleId,
        lesson_id: lessonId,
        q1: parseInt(form.q1.value),
        q2: parseInt(form.q2.value),
        q3: parseInt(form.q3.value),
        q4: parseInt(form.q4.value),
        q5: parseInt(form.q5.value),
      };
  
      try {
        const res = await fetch("/api/feedback.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`
          },
          body: JSON.stringify(data)
        });
  
        const result = await res.json();
        if (result.success) {
          window.location.href = `./roadmap.html?modul_id=${moduleId}`;
        } else {
          alert("Fehler beim Speichern.");
        }
      } catch (err) {
        console.error(err);
        alert("Verbindung zum Server fehlgeschlagen.");
      }
    });
  });