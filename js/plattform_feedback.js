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