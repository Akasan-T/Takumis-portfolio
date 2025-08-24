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
      const itemTypes = item.dataset.type.split(',');
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

document.addEventListener("DOMContentLoaded", () => {
  const overlay = document.getElementById("overlay");
  const modal = document.getElementById("modal");
  const modalContent = modal.querySelector(".modal-content");
  const closeModal = document.getElementById("closeModal");

  let currentImages = [];
  let currentIndex = 0;

  // すべての作品にクリックイベント
  document.querySelectorAll(".production_box .item").forEach(item => {
    item.addEventListener("click", e => {
      e.preventDefault();

      // 作品情報取得
      const title = item.querySelector("h3").innerText;
      const desc = item.querySelector("p").innerText;
      currentImages = JSON.parse(item.dataset.images || '[]');
      currentIndex = 0;

      // モーダル内容を生成
      modalContent.innerHTML = `
        <div id="detail_box_left">
          <div id="modal-images" style="">
            <button id="prev-img">◀︎</button>
            <img id="modal-img" src="${currentImages[0] || item.querySelector("img").src}" style="max-width:90%; height:330px; margin:10px; border-radius: 12px;">
            <button id="next-img">▶︎</button>
          </div>
            <h2 id="detail_box_title">${title}</h2>
            <p id="tag">${desc}</p>
            <p id="" 
        </div>
        <div>
          
        </div>
      `;

      overlay.style.display = "block";
      modal.style.display = "block";

      // 画像切替
      document.getElementById("next-img").addEventListener("click", () => {
        if(currentImages.length === 0) return;
        currentIndex = (currentIndex + 1) % currentImages.length;
        document.getElementById("modal-img").src = currentImages[currentIndex];
      });

      document.getElementById("prev-img").addEventListener("click", () => {
        if(currentImages.length === 0) return;
        currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length;
        document.getElementById("modal-img").src = currentImages[currentIndex];
      });
    });
  });

  // モーダル閉じる
  function closeModalFunc() {
    overlay.style.display = "none";
    modal.style.display = "none";
  }

  closeModal.addEventListener("click", closeModalFunc);
  overlay.addEventListener("click", closeModalFunc);
});