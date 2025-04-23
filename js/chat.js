document.addEventListener("DOMContentLoaded", async () => {
    const params = new URLSearchParams(window.location.search);
    const lessonId = params.get("lesson_id");
    const token = localStorage.getItem("token");
    const chatBody = document.getElementById("chat-body");
    const input = document.querySelector(".chat-input-wrapper input");
    const sendBtn = document.querySelector(".chat-input-wrapper button");
  
    let chatId = null;
  
    async function loadLessonInfo() {
        const res = await fetch(`/api/lesson.php?lesson_id=${lessonId}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        const data = await res.json();
        document.getElementById("lesson-titel").textContent = data.title;
        document.getElementById("lesson-subtitle").textContent = data.description;
    }

    async function loadChat() {
        const res = await fetch(`/api/chat.php?lesson_id=${lessonId}`, {
            headers: { Authorization: `Bearer ${token}` }
        });
        const data = await res.json();
        chatId = data.chat_id;
    
        chatBody.innerHTML = "";
        data.messages.forEach(msg => appendMessage(msg.sender, msg.message));
        scrollToBottom();
    }
  
    function appendMessage(sender, text) {
        const div = document.createElement("div");
        div.classList.add("message");
        div.classList.add(sender === "user" ? "from-user" : "from-ai");
        div.textContent = text;
        chatBody.appendChild(div);
    }
  
    async function sendMessage() {
        const text = input.value.trim();
        if (!text || !chatId) return;
    
        appendMessage("user", text);
        scrollToBottom();
        input.value = "";
        input.disabled = true;
        sendBtn.disabled = true;
    
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
        scrollToBottom();
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
  
    loadLessonInfo();
    loadChat();
});