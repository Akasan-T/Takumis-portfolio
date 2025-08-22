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

  ['type-filters', 'tech-filters', 'year-filters'].forEach(setupFilters);

  function filterItems() {
    const typeChecked = Array.from(document.querySelectorAll('#type-filters input:checked')).map(cb => cb.value);
    const techChecked = Array.from(document.querySelectorAll('#tech-filters input:checked')).map(cb => cb.value);
    const yearChecked = Array.from(document.querySelectorAll('#year-filters input:checked')).map(cb => cb.value);

    const items = document.querySelectorAll('#items .item');

    items.forEach(item => {
      const itemTypes = item.dataset.type.split(',');z
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

    // 表示されるアイテムにGSAPアニメーション
    const visibleItems = Array.from(document.querySelectorAll('#items .item:not(.hidden)'));
    gsap.fromTo(
      visibleItems,
      { autoAlpha: 0, y: 20 },
      {
        autoAlpha: 1,
        y: 0,
        duration: 0.4,
        stagger: 0.1,
        ease: "power1.out"
      }
    );
  }

  window.resetFilter = function() {
    document.querySelectorAll('.filter-btn input').forEach(cb => cb.checked = false);
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('selected'));
    document.querySelectorAll('.item').forEach(item => item.classList.remove('hidden'));
    
    // 全表示時もアニメーション
    const allItems = Array.from(document.querySelectorAll('#items .item'));
    gsap.fromTo(
      allItems,
      { autoAlpha: 0, y: 20 },
      {
        autoAlpha: 1,
        y: 0,
        duration: 0.4,
        stagger: 0.1,
        ease: "power1.out"
      }
    );
  }

});