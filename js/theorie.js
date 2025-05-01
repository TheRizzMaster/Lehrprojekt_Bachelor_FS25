document.addEventListener("DOMContentLoaded", async () => {
    const params = new URLSearchParams(window.location.search);
    const moduleId = params.get("module_id");
    const lessonId = params.get("lesson_id");
    const titleEl = document.querySelector(".lesson-header h1");
    const subtitleEl = document.querySelector(".lesson-header .subtitle");
    const card = document.querySelector(".lesson-card");
    const chatButton = document.querySelector("#chat-button");
  
    if (!lessonId) {
      titleEl.textContent = "Fehler";
      card.innerHTML = "<p>Keine lesson_id angegeben.</p>";
      return;
    }
  
    const token = localStorage.getItem("token");
    const res = await fetch(`/api/lesson.php?lesson_id=${lessonId}`, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
  
    const data = await res.json();
  
    if (!res.ok) {
      titleEl.textContent = "Fehler";
      card.innerHTML = `<p>${data.error}</p>`;
      return;
    }
  
    titleEl.textContent = data.title;
    subtitleEl.textContent = data.description;
    card.innerHTML = "<h2>Theorie</h2>";
  
    data.theory_content.forEach(block => {
      if (block.type === "paragraph") {
        const p = document.createElement("p");
        p.textContent = block.text;
        card.appendChild(p);
      } else if (block.type === "list") {
        const ul = document.createElement("ul");
        block.items.forEach(item => {
          const li = document.createElement("li");
          li.textContent = item;
          ul.appendChild(li);
        });
        card.appendChild(ul);
      } else if (block.type === "image") {
        const img = document.createElement("img");
        img.src = block.url;
        img.alt = block.alt || "";
        img.classList.add("theory-image");
        card.appendChild(img);
      } else if (block.type === "video") {
        const iframe = document.createElement("iframe");
        iframe.src = block.src;
        iframe.frameBorder = "0";
        iframe.allow = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture";
        iframe.allowFullscreen = true;
        iframe.classList.add("theory-video");
        card.appendChild(iframe);
      } else if (block.type === "audio") {
        const audio = document.createElement("audio");
        audio.src = block.src;
        audio.controls = true;
        audio.classList.add("theory-audio");
        card.appendChild(audio);
      }
    });

    chatButton.addEventListener("click", () => {
      window.location.href = `./chat.html?module_id=${moduleId}&lesson_id=${lessonId}`;
    });

});