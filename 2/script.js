document.addEventListener("mousemove", (e) => {
  const sparkle = document.createElement("div");
  sparkle.classList.add("sparkle");
  sparkle.style.left = `${e.clientX}px`;
  sparkle.style.top = `${e.clientY}px`;
  document.body.appendChild(sparkle);
  setTimeout(() => sparkle.remove(), 500);
});
function filterByLetter(letter) {
  let rows = document.querySelectorAll('#userTableBody tr');
  let visibleCount = 0;
  rows.forEach(row => {
    let nom = row.cells[2].textContent.trim().toUpperCase();
    if (letter === 'ALL' || nom.startsWith(letter)) {
      row.style.display = '';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });
  document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
  searchInput.value = ''; // reset search
}
window.addEventListener('DOMContentLoaded', () => {
  const alert = document.getElementById('alertMessage');
  if (alert) {
    // تأكد من بدء الأنيميشن (موجود في CSS)
    alert.style.animation = 'slideDownFadeIn 4s ease forwards';

    // بعد 4 ثواني، نخفي العنصر نهائياً
    setTimeout(() => {
      alert.style.display = 'none';
    }, 4000);
  }
});
