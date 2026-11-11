  </div><!-- end .admin-content -->
</div><!-- end .admin-main -->
</div><!-- end .admin-wrapper -->

<!-- Delete Confirm Modal -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal-box">
    <div class="modal-title" style="display:flex;align-items:center;gap:8px;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--admin-warning);"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      Konfirmasi Hapus
    </div>
    <div class="modal-body" id="deleteModalBody">Apakah Anda yakin ingin menghapus item ini? Tindakan ini tidak dapat dibatalkan.</div>
    <div class="modal-footer">
      <button class="btn btn-outline btn-sm" onclick="closeDeleteModal()">Batal</button>
      <form id="deleteForm" method="POST" style="display:inline">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteModalId">
        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
      </form>
    </div>
  </div>
</div>

<script>
// Sidebar toggle (mobile)
const sidebarToggle = document.getElementById('sidebarToggle');
if (window.innerWidth <= 768 && sidebarToggle) sidebarToggle.style.display = 'block';
window.addEventListener('resize', () => {
  if (sidebarToggle) sidebarToggle.style.display = window.innerWidth <= 768 ? 'block' : 'none';
});

// Delete modal
function confirmDelete(id, label, formAction) {
  document.getElementById('deleteModalId').value = id;
  document.getElementById('deleteModalBody').textContent = 'Hapus: ' + label + '?';
  document.getElementById('deleteForm').action = formAction || window.location.pathname;
  document.getElementById('deleteModal').classList.add('active');
}
function closeDeleteModal() {
  document.getElementById('deleteModal').classList.remove('active');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
  if (e.target === this) closeDeleteModal();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDeleteModal(); });

// Auto-hide alerts
document.querySelectorAll('.alert').forEach(el => {
  setTimeout(() => { el.style.opacity='0'; el.style.transition='opacity 0.5s'; setTimeout(() => el.remove(), 500); }, 5000);
});
</script>
<?php if (!empty($adminExtraJs)) echo $adminExtraJs; ?>
</body>
</html>
