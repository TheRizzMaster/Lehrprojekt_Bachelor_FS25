document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("plattform-feedback-form");
  
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
  
      const token = localStorage.getItem("token");
      const formData = new FormData(form);
  
      const payload = {
        helpful: formData.get("helpful"),
        reasons: formData.get("reasons"),
        improved: formData.get("improved"),
        learn_effective: formData.get("learn_effective"),
        general_feedback: formData.get("general_feedback")
      };
  
      const res = await fetch("/api/plattform_feedback.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": "Bearer " + token
        },
        body: JSON.stringify(payload)
      });
  
      if (res.ok) {
        alert("Vielen Dank für dein Feedback!");
        window.location.href = "/dashboard.html";
      } else {
        alert("Fehler beim Speichern des Feedbacks.");
      }
    });


  });

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