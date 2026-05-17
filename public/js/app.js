document.addEventListener("DOMContentLoaded", () => {
  initScrollAnimations();
  initDeleteModals();
});

function showToast(message) {
  const container = document.getElementById("toast-container");
  if (!container) return;

  const toast = document.createElement("div");
  toast.className = "toast";

  const text = document.createElement("span");
  text.innerText = message;

  const progress = document.createElement("div");
  progress.className = "toast-progress";

  toast.appendChild(text);
  toast.appendChild(progress);

  container.appendChild(toast);

  requestAnimationFrame(() => {
    progress.style.width = "0%";
  });

  setTimeout(() => {
    toast.style.opacity = "0";
    setTimeout(() => toast.remove(), 500);
  }, 3000);
}

function initDeleteModals() {
  const modal = document.getElementById("deleteModal");
  const confirmBtn = document.getElementById("confirmDelete");
  const cancelBtn = document.getElementById("cancelDelete");
  if (!modal) return;

  let deleteUrl = "";

  document.addEventListener("click", (e) => {
    if (e.target.classList.contains("delete-btn")) {
      e.preventDefault();
      deleteUrl = e.target.href;
      modal.classList.add("active");
    }
  });

  cancelBtn?.addEventListener('click', () => modal.classList.remove('active'));
  confirmBtn?.addEventListener('click', () => {
    if (deleteUrl) window.location.href = deleteUrl;
  });
}

function initScrollAnimations() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('show');
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
}
