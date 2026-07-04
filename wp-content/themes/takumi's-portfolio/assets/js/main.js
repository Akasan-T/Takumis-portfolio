/* ============================================================
   共通スクリプト — ローダー / ナビ / スクロール演出
   ============================================================ */

/* ---------- ローダー ---------- */
window.addEventListener("load", () => {
  const loader = document.querySelector(".loader");
  if (loader) {
    setTimeout(() => loader.classList.add("is-done"), 500);
  }
});
// 読み込みが長引いても最悪 3.5 秒で解除する
setTimeout(() => {
  document.querySelector(".loader")?.classList.add("is-done");
}, 3500);

/* ---------- ヒーロー: 実写の富士山 → 3Dワイヤーフレームへ ---------- */
const heroPhoto = document.querySelector(".hero__photo");
if (heroPhoto) {
  const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  let dissolved = false;
  const dissolve = () => {
    if (dissolved) return;
    dissolved = true;
    heroPhoto.classList.add("is-gone");
    window.dispatchEvent(new Event("takumi:dissolve"));
  };
  if (reduced) {
    dissolve();
  } else {
    // ローダー解除後、写真とキャッチコピーを見せてから3Dへクロスフェード
    window.addEventListener("load", () => setTimeout(dissolve, 3200));
    setTimeout(dissolve, 5800); // 保険
  }
}

/* ---------- ヘッダー(スクロールで背景付与) ---------- */
const header = document.querySelector(".site-header");
function onScrollHeader() {
  header?.classList.toggle("is-scrolled", window.scrollY > 40);
}
window.addEventListener("scroll", onScrollHeader, { passive: true });
onScrollHeader();

/* ---------- ハンバーガーメニュー ---------- */
const navToggle = document.querySelector(".nav-toggle");
const globalNav = document.querySelector(".global-nav");
navToggle?.addEventListener("click", () => {
  const open = navToggle.classList.toggle("is-open");
  globalNav?.classList.toggle("is-open", open);
  document.body.style.overflow = open ? "hidden" : "";
});
globalNav?.querySelectorAll("a").forEach((a) =>
  a.addEventListener("click", () => {
    navToggle?.classList.remove("is-open");
    globalNav.classList.remove("is-open");
    document.body.style.overflow = "";
  })
);

/* ---------- スクロールリビール ---------- */
const revealObserver = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("is-visible");
        revealObserver.unobserve(entry.target);
      }
    });
  },
  { threshold: 0.15, rootMargin: "0px 0px -40px 0px" }
);
document.querySelectorAll("[data-reveal]").forEach((el) => revealObserver.observe(el));

/* ---------- スキルバー ---------- */
const barObserver = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const fill = entry.target;
        fill.style.width = (fill.dataset.width || 0) + "%";
        barObserver.unobserve(fill);
      }
    });
  },
  { threshold: 0.4 }
);
document.querySelectorAll(".skill-bar .fill").forEach((el) => barObserver.observe(el));

/* ---------- ページトップ ---------- */
const pagetop = document.querySelector(".pagetop");
window.addEventListener(
  "scroll",
  () => pagetop?.classList.toggle("is-visible", window.scrollY > 600),
  { passive: true }
);
pagetop?.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));

/* ---------- GSAP 演出(読み込まれている場合のみ) ---------- */
if (window.gsap && window.ScrollTrigger) {
  gsap.registerPlugin(ScrollTrigger);
  const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  if (!reduced) {
    // ヒーローの文字を順に立ち上げる(写真→3Dの切り替え後に表示)
    const heroEls = ".hero__eyebrow, .hero__title, .hero__lead, .hero__climb, .hero__scroll";
    const rise = { y: 0, opacity: 1, duration: 1.2, stagger: 0.15, ease: "power3.out" };
    if (document.querySelector(".hero")) {
      gsap.set(heroEls, { y: 40, opacity: 0 });
      if (document.querySelector(".hero__photo")) {
        window.addEventListener(
          "takumi:dissolve",
          () => gsap.to(heroEls, Object.assign({ delay: 0.7 }, rise)),
          { once: true }
        );
      } else {
        gsap.to(heroEls, Object.assign({ delay: 0.9 }, rise));
      }
    }

    // セクション見出しのライン演出
    document.querySelectorAll(".section-head").forEach((headEl) => {
      gsap.from(headEl, {
        scrollTrigger: { trigger: headEl, start: "top 85%" },
        y: 30,
        opacity: 0,
        duration: 1,
        ease: "power3.out",
      });
    });
  }
}
