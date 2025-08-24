document.addEventListener('DOMContentLoaded', () => {
  const fills = document.querySelectorAll('.skill-bar .fill');

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const target = entry.target;
        const targetWidth = target.dataset.width;
        target.style.width = targetWidth + '%';
        observer.unobserve(target); // 1回だけ実行したい場合は監視解除
      }
    });
  }, { threshold: 0.3 }); // 30%見えたら発火

  fills.forEach(fill => observer.observe(fill));
});