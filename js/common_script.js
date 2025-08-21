document.addEventListener('DOMContentLoaded', () => {

  function setupFilters(containerId) {
    const container = document.getElementById(containerId);
    if(!container) return;
    container.querySelectorAll('.filter-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault(); // フォーカス青枠対策
        const cb = btn.querySelector('input');
        if(cb) cb.checked = !cb.checked;
        btn.classList.toggle('selected'); // 色切替
        filterItems();
      });
    });
  }

  ['type-filters','tech-filters','year-filters'].forEach(setupFilters);

  function filterItems() {
    const typeChecked = Array.from(document.querySelectorAll('#type-filters input:checked')).map(cb=>cb.value);
    const techChecked = Array.from(document.querySelectorAll('#tech-filters input:checked')).map(cb=>cb.value);
    const yearChecked = Array.from(document.querySelectorAll('#year-filters input:checked')).map(cb=>cb.value);

    document.querySelectorAll('#items .item').forEach(item => {
      const typeMatch = typeChecked.length === 0 || typeChecked.includes(item.dataset.type);
      const techMatch = techChecked.length === 0 || techChecked.some(t => item.dataset.tech.split(',').includes(t));
      const yearMatch = yearChecked.length === 0 || yearChecked.includes(item.dataset.year);

      if(typeMatch && techMatch && yearMatch){
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