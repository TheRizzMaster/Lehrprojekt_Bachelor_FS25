// admin.js – final version mit Drag & Drop, Modal-Tabs, JSON-Validation

let aceTheoryEditor, aceChatEditor, draggedItem = null;

const modal = document.getElementById("modal");
const modalTitle = document.getElementById("modal-title");
const modalForm = document.getElementById("modal-form");
const modalCancelBtn = document.getElementById("modal-cancel-btn");
const courseList = document.getElementById("course-list");
const addCourseBtn = document.getElementById("add-course-btn");

let currentEditType = null;
let currentEditId = null;
let parentId = null;

function activateTabs() {
  document.querySelectorAll(".tab-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
      document.querySelectorAll(".tab-content").forEach(tc => tc.classList.add("hidden"));
      btn.classList.add("active");
      document.getElementById("tab-" + btn.dataset.tab).classList.remove("hidden");
    });
  });
}

function buildLessonTabs(data = {}) {
  return `
    <div class="tabs">
      <button type="button" class="tab-btn active" data-tab="basic">Basis</button>
      <button type="button" class="tab-btn" data-tab="theory">Theorie</button>
      <button type="button" class="tab-btn" data-tab="chat">Chat-Konfiguration</button>
    </div>
    <div class="tab-content" id="tab-basic">
      <label>Titel:</label>
      <input type="text" id="item-title" value="${data.title || ""}" required>
      <label>Beschreibung:</label>
      <textarea id="item-description">${data.description || ""}</textarea>
      <label>CT-Phase:</label>
      <select id="item-ct">
        <option value="">-- bitte wählen --</option>
        <option value="Decomposition">Decomposition</option>
        <option value="Abstraction">Abstraction</option>
        <option value="Pattern Recognition">Pattern Recognition</option>
        <option value="Algorithm Design">Algorithm Design</option>
      </select>
    </div>
    <div class="tab-content hidden" id="tab-theory">
      <div id="editor-theory" style="height:200px; width:100%"></div>
    </div>
    <div class="tab-content hidden" id="tab-chat">
      <div id="editor-chat" style="height:200px; width:100%"></div>
    </div>`;
}

function openModal(type, id = null, data = {}, parent = null) {
  modal.classList.remove("hidden");
  currentEditType = type;
  currentEditId = id;
  parentId = parent;
  modalTitle.textContent = id ? `Bearbeite ${type}` : `Neues ${type} erstellen`;

  if (type === "lesson") {
    modalForm.innerHTML = buildLessonTabs(data);
    setTimeout(() => {
      aceTheoryEditor = ace.edit("editor-theory");
      aceTheoryEditor.session.setMode("ace/mode/json");
      aceTheoryEditor.setValue(JSON.stringify(data.theory_content ? JSON.parse(data.theory_content) : [], null, 2));
      aceTheoryEditor.clearSelection();

      aceChatEditor = ace.edit("editor-chat");
      aceChatEditor.session.setMode("ace/mode/json");
      aceChatEditor.setValue(JSON.stringify(data.chat_config ? JSON.parse(data.chat_config) : {}, null, 2));
      aceChatEditor.clearSelection();

      if (data.ct_phase) document.getElementById("item-ct").value = data.ct_phase;
      activateTabs();
    }, 50);
  } else {
    modalForm.innerHTML = `
      <label>Titel:</label>
      <input type="text" id="item-title" value="${data.title || ""}" required>
      <label>Beschreibung:</label>
      <textarea id="item-description">${data.description || ""}</textarea>`;
  }
}

modalCancelBtn.addEventListener("click", () => {
  modal.classList.add("hidden");
  modalForm.reset();
});

modalForm.addEventListener("submit", async (e) => {
  e.preventDefault();
  const payload = {
    title: document.getElementById("item-title").value,
    description: document.getElementById("item-description").value
  };

  if (currentEditType === "lesson") {
    try {
      payload.theory_content = JSON.parse(aceTheoryEditor.getValue());
      payload.chat_config = JSON.parse(aceChatEditor.getValue());
      payload.ct_phase = document.getElementById("item-ct").value;
    } catch (err) {
      alert("Ungültiges JSON: " + err.message);
      return;
    }
  }
  if (parentId) payload.parent_id = parentId;

  const method = currentEditId ? "PUT" : "POST";
  const url = `../api/admin_${currentEditType}.php${currentEditId ? `?id=${currentEditId}` : ""}`;

  await fetch(url, {
    method,
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  });
  modal.classList.add("hidden");
  loadCourses();
});

addCourseBtn.addEventListener("click", () => openModal("course"));

courseList.addEventListener("click", async (e) => {
  const id = e.target.dataset.id;
  const type = e.target.dataset.type;

  if (e.target.classList.contains("edit-btn")) {
    const res = await fetch(`../api/admin_${type}.php?id=${id}`);
    const data = await res.json();
    openModal(type, id, data);
  }

  if (e.target.classList.contains("view-btn")) {
    if (type === "module") loadModules(id);
    if (type === "lesson") loadLessons(id);
  }

  if (e.target.classList.contains("add-module-btn")) openModal("module", null, {}, id);
  if (e.target.classList.contains("add-lesson-btn")) openModal("lesson", null, {}, id);
});

async function loadCourses() {
  const res = await fetch("../api/admin_course.php");
  const data = await res.json();
  courseList.innerHTML = "";
  data.forEach(course => {
    courseList.insertAdjacentHTML("beforeend", `
      <div class="list-item">
        <strong>${course.title}</strong>
        <div class="item-actions">
          <button class="edit-btn" data-id="${course.id}" data-type="course">✏️</button>
          <button class="view-btn" data-id="${course.id}" data-type="module">👁️</button>
          <button class="add-module-btn" data-course-id="${course.id}">➕ Modul</button>
        </div>
        <div id="modules-${course.id}" class="module-list hidden"></div>
      </div>`);
  });
}

async function loadModules(courseId) {
  const res = await fetch(`../api/admin_module.php?course_id=${courseId}`);
  const data = await res.json();
  const list = document.getElementById(`modules-${courseId}`);
  list.classList.toggle("hidden");
  list.innerHTML = "";

  data.forEach((mod, idx) => {
    const el = document.createElement("div");
    el.classList.add("list-item", "nested-item");
    el.setAttribute("draggable", true);
    el.dataset.id = mod.id;
    el.innerHTML = `
      <strong>${mod.title}</strong>
      <div class="item-actions">
        <button class="edit-btn" data-id="${mod.id}" data-type="module">✏️</button>
        <button class="view-btn" data-id="${mod.id}" data-type="lesson">👁️</button>
        <button class="add-lesson-btn" data-module-id="${mod.id}">➕ Lektion</button>
      </div>
      <div id="lessons-${mod.id}" class="lesson-list hidden"></div>`;

    el.addEventListener("dragstart", (e) => {
      draggedItem = el;
    });

    el.addEventListener("dragover", (e) => e.preventDefault());

    el.addEventListener("drop", async (e) => {
      e.preventDefault();
      if (draggedItem !== el) {
        el.parentNode.insertBefore(draggedItem, el);
        saveOrder(`../api/admin_module.php?action=reorder`, `#modules-${courseId}`);
      }
    });
    list.appendChild(el);
  });
}

async function loadLessons(moduleId) {
  const res = await fetch(`../api/admin_lesson.php?module_id=${moduleId}`);
  const data = await res.json();
  const list = document.getElementById(`lessons-${moduleId}`);
  list.classList.toggle("hidden");
  list.innerHTML = "";

  data.forEach(lesson => {
    const el = document.createElement("div");
    el.classList.add("list-item", "nested-item", "deep-item");
    el.setAttribute("draggable", true);
    el.dataset.id = lesson.id;
    el.innerHTML = `
      <strong>${lesson.title}</strong>
      <div class="item-actions">
        <button class="edit-btn" data-id="${lesson.id}" data-type="lesson">✏️</button>
      </div>`;

    el.addEventListener("dragstart", (e) => {
      draggedItem = el;
    });

    el.addEventListener("dragover", (e) => e.preventDefault());

    el.addEventListener("drop", async (e) => {
      e.preventDefault();
      if (draggedItem !== el) {
        el.parentNode.insertBefore(draggedItem, el);
        saveOrder(`../api/admin_lesson.php?action=reorder`, `#lessons-${moduleId}`);
      }
    });
    list.appendChild(el);
  });
}

async function saveOrder(endpoint, selector) {
  const items = document.querySelectorAll(`${selector} .list-item`);
  const order = Array.from(items).map((el, i) => ({ id: el.dataset.id, position: i + 1 }));
  await fetch(endpoint, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ order })
  });
}

loadCourses();
