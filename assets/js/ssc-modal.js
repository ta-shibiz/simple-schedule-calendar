document.addEventListener('DOMContentLoaded', () => {

  const modal = document.getElementById('ssc-modal');
  if (!modal) return;

  const titleEl = document.getElementById('ssc-modal-title');
  const bodyEl  = document.getElementById('ssc-modal-body');
  const editEl  = modal.querySelector('.ssc-edit-link a');

  document.querySelectorAll('.ssc-item').forEach(item => {
    item.addEventListener('click', () => {

      // =========================
      // ★リンク分岐（追加）
      // =========================
      const link = item.dataset.link;

      if (link) {
        window.location.href = link;
        return;
      }

      // =========================
      // 通常モーダル
      // =========================
      titleEl.innerHTML = item.dataset.title;
      bodyEl.innerHTML  = item.dataset.body;

      if (editEl && item.dataset.edit) {
        editEl.href = item.dataset.edit;
      }

      modal.classList.add('show');
    });
  });

  modal.querySelector('.ssc-modal-bg').addEventListener('click', close);
  modal.querySelector('.ssc-modal-close').addEventListener('click', close);

  function close() {
    modal.classList.remove('show');
  }
});