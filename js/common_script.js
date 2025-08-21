document.addEventListener('DOMContentLoaded', () => {

  function setupFilters(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.querySelectorAll('.filter-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault(); // フォーカス青枠対策
        const cb = btn.querySelector('input');
        if (cb) cb.checked = !cb.checked;
        btn.classList.toggle('selected'); // ボタン色切替
        filterItems();
      });
    });
  }

  // 初期設定
  ['type-filters', 'tech-filters', 'year-filters'].forEach(setupFilters);

  function filterItems() {
    const typeChecked = Array.from(document.querySelectorAll('#type-filters input:checked')).map(cb => cb.value);
    const techChecked = Array.from(document.querySelectorAll('#tech-filters input:checked')).map(cb => cb.value);
    const yearChecked = Array.from(document.querySelectorAll('#year-filters input:checked')).map(cb => cb.value);

    document.querySelectorAll('#items .item').forEach(item => {
      // 複数カテゴリ対応
      const itemTypes = item.dataset.type.split(','); // 複数タイプ対応
      const itemTechs = item.dataset.tech ? item.dataset.tech.split(',') : [];
      const itemYear = item.dataset.year;

      const typeMatch = typeChecked.length === 0 || typeChecked.some(t => itemTypes.includes(t));
      const techMatch = techChecked.length === 0 || techChecked.some(t => itemTechs.includes(t));
      const yearMatch = yearChecked.length === 0 || yearChecked.includes(itemYear);

      if (typeMatch && techMatch && yearMatch) {
        item.classList.remove('hidden');
      } else {
        item.classList.add('hidden');
      }
    });
  }

  window.resetFilter = function() {
    document.querySelectorAll('.filter-btn input').forEach(cb => cb.checked = false);
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('selected'));
    document.querySelectorAll('.item').forEach(item => item.classList.remove('hidden'));
  }

});

// セレクタ名（.pagetop）に一致する要素を取得
const pagetop_btn = document.querySelector(".pagetop");
// .pagetopをクリックしたら
pagetop_btn.addEventListener("click", scroll_top);
// ページ上部へスムーズに移動
function scroll_top() {
    window.scroll({ top: 0, behavior: "smooth" });
}
// スクロールされたら表示
window.addEventListener("scroll", scroll_event);
function scroll_event() {
    if (window.pageYOffset > 100) {
        pagetop_btn.style.opacity = "1";
    } else if (window.pageYOffset < 100) {
        pagetop_btn.style.opacity = "0";
    }
}