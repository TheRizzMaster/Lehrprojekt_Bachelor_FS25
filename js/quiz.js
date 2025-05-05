document.getElementById("quiz-form").addEventListener("submit", async function (e) {
    e.preventDefault();
  
    const formData = new FormData(e.target);
    const answers = {};
  
    for (let [name, value] of formData.entries()) {
      answers[name] = value;
    }

    console.log(answers);
  
    try {
      const res = await fetch("./api/submit_quiz.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": "Bearer " + localStorage.getItem("token")
        },
        body: JSON.stringify(answers)
      });
  
      const result = await res.json();
  
      if (res.ok) {
        if(result.success) {
          alert("Quiz erfolgreich eingereicht!");
        } else {
            alert("Quiz nicht erfolgreich eingereicht: " + result.message);
        }
        // alert(`Dein Ergebnis: ${result.score} von 16 Punkten`);
        // window.location.href = "./dashboard.html"; // oder eine andere Seite
      } else {
        alert("Fehler: " + (result.message || "Unbekannter Fehler"));
      }
    } catch (err) {
      console.error("Fehler beim Senden:", err);
      alert("Verbindung fehlgeschlagen.");
    }
});