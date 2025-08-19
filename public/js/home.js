// JS nhẹ cho các tương tác chung

// Xác nhận xoá ở các form có onsubmit confirm
document.addEventListener('click', function (e) {
  const btn = e.target.closest('form[data-confirm] button[type="submit"]');
  if (btn) {
    const form = btn.closest('form');
    const msg = form.getAttribute('data-confirm') || 'Bạn chắc chắn muốn thực hiện?';
    if (!confirm(msg)) {
      e.preventDefault();
      e.stopPropagation();
    }
  }
});

// (Tuỳ chọn) Tự ẩn alert sau 3s
setTimeout(() => {
  document.querySelectorAll('.alert.auto-hide').forEach(el => {
    el.classList.add('fade');
    setTimeout(() => el.remove(), 300);
  });
}, 3000);
