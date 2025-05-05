document.getElementById("plattform-feedback-form").addEventListener("submit", async (e) => {
    e.preventDefault();
  
    const form = e.target;
    const token = localStorage.getItem("token");
  
    const payload = {};
    for (let i = 1; i <= 16; i++) {
      payload[`q${i}`] = Number(form[`q${i}`].value);
    }
  
    payload.positive = form.positive.value.trim();
    payload.suggestions = form.suggestions.value.trim();
    payload.comments = form.comments.value.trim();
  
    fetch("/api/submit_platform_feedback.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": "Bearer " + localStorage.getItem("token")
        },
        body: JSON.stringify(payload)
      })
      .then(res => res.text())  // <-- zuerst als Text lesen
      .then(data => {
        console.log("Serverantwort:", data); // zum Debuggen
        const json = JSON.parse(data); // dann manuell parsen
        console.log("JSON:", json);
      })
      .catch(err => console.error("Fehler beim Absenden:", err));
  
    // const result = await res.json();
    // if (result.success) {
    //   alert("Danke für dein Feedback!");
    //   window.location.href = "/dashboard.html";
    // } else {
    //   alert("Fehler: " + (result.error || "Unbekannter Fehler"));
    // }
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