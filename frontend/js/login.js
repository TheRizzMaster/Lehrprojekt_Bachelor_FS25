function showForm(id) {
    // Formulare umschalten
    document.getElementById('login-form').classList.remove('active');
    document.getElementById('register-form').classList.remove('active');
    document.getElementById(id).classList.add('active');
  
    // Tab-Schaltflächen aktualisieren
    const buttons = document.querySelectorAll('.tabs button');
    buttons.forEach(btn => btn.classList.remove('active'));
  
    if (id === 'login-form') {
      buttons[0].classList.add('active');
    } else {
      buttons[1].classList.add('active');
    }
  }