document.addEventListener("DOMContentLoaded", async () => {
    const params = new URLSearchParams(window.location.search);
    const lessonId = params.get("lesson_id");
    const token = localStorage.getItem("token");
  
    const chatBody = document.getElementById("chat-body");
    const input = document.querySelector(".chat-input-wrapper input");
    const sendBtn = document.querySelector(".chat-input-wrapper button");
  
    const titleEl = document.getElementById("lesson-titel");
    const subtitleEl = document.getElementById("lesson-subtitle");
  
    let chatId = null;
  
    async function loadLessonInfo() {
      const res = await fetch(`/api/lesson.php?lesson_id=${lessonId}`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      const data = await res.json();
      titleEl.textContent = data.title;
      subtitleEl.textContent = data.description;
    }
  
    async function loadChat() {
      const res = await fetch(`/api/chat.php?lesson_id=${lessonId}`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      const data = await res.json();
      chatId = data.chat_id;
  
      chatBody.innerHTML = "";
      data.messages.forEach(msg => {
        appendMessage(msg.sender, msg.message);
      });
  
      scrollToBottom();
    }
  
    function appendMessage(sender, text) {
      const div = document.createElement("div");
      div.classList.add("message");
  
      if (sender === "user") {
        div.classList.add("from-user");
      } else if (sender === "system") {
        div.classList.add("from-ai"); // Optional: eigene Klasse .from-system
        div.style.opacity = "0.7";
        div.style.fontStyle = "italic";
      } else {
        div.classList.add("from-ai");
      }
  
      div.textContent = text;
      chatBody.appendChild(div);
  
      // Scroll delay for DOM paint
      setTimeout(() => {
        scrollToBottom();
      }, 10);
    }
  
    async function sendMessage() {
      const text = input.value.trim();
      if (!text || !chatId) return;
  
      appendMessage("user", text);
      input.value = "";
      input.disabled = true;
      sendBtn.disabled = true;
  
      try {
        const res = await fetch("/api/chat.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${token}`
          },
          body: JSON.stringify({ chat_id: chatId, message: text })
        });
  
        const data = await res.json();
        appendMessage("ai", data.response);
      } catch (err) {
        appendMessage("ai", "Fehler beim Laden der Antwort.");
      }
  
      input.disabled = false;
      sendBtn.disabled = false;
      input.focus();
    }
  
    function scrollToBottom() {
      chatBody.scrollTop = chatBody.scrollHeight;
    }
  
    sendBtn.addEventListener("click", sendMessage);
    input.addEventListener("keydown", e => {
      if (e.key === "Enter") sendMessage();
    });
  
    await loadLessonInfo();
    await loadChat();
  });