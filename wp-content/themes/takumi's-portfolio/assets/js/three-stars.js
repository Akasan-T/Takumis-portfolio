/* ============================================================
   Stars Background — サブページ/セクション用の星空パーティクル
   .stars-canvas を持つ canvas すべてに適用 (Three.js / CDN)
   ============================================================ */
import * as THREE from "https://unpkg.com/three@0.160.0/build/three.module.js";

const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

function supportsWebGL() {
  try {
    const c = document.createElement("canvas");
    return !!(window.WebGLRenderingContext && (c.getContext("webgl") || c.getContext("experimental-webgl")));
  } catch (e) {
    return false;
  }
}

if (supportsWebGL()) {
  document.querySelectorAll("canvas.stars-canvas").forEach(initStars);
}

function initStars(canvas) {
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(60, 1, 0.1, 120);
  camera.position.z = 20;

  /* 星 */
  const STAR_COUNT = 500;
  const starGeo = new THREE.BufferGeometry();
  const pos = new Float32Array(STAR_COUNT * 3);
  for (let i = 0; i < STAR_COUNT; i++) {
    pos[i * 3] = (Math.random() - 0.5) * 90;
    pos[i * 3 + 1] = (Math.random() - 0.5) * 50;
    pos[i * 3 + 2] = (Math.random() - 0.5) * 40;
  }
  starGeo.setAttribute("position", new THREE.BufferAttribute(pos, 3));
  const stars = new THREE.Points(
    starGeo,
    new THREE.PointsMaterial({
      color: 0xbcd0f7,
      size: 0.14,
      transparent: true,
      opacity: 0.8,
      depthWrite: false,
    })
  );
  scene.add(stars);

  /* 紅の粒子(アクセント) */
  const EMBER_COUNT = 40;
  const emberGeo = new THREE.BufferGeometry();
  const epos = new Float32Array(EMBER_COUNT * 3);
  for (let i = 0; i < EMBER_COUNT; i++) {
    epos[i * 3] = (Math.random() - 0.5) * 80;
    epos[i * 3 + 1] = (Math.random() - 0.5) * 44;
    epos[i * 3 + 2] = (Math.random() - 0.5) * 30;
  }
  emberGeo.setAttribute("position", new THREE.BufferAttribute(epos, 3));
  const embers = new THREE.Points(
    emberGeo,
    new THREE.PointsMaterial({
      color: 0xe94560,
      size: 0.18,
      transparent: true,
      opacity: 0.7,
      depthWrite: false,
      blending: THREE.AdditiveBlending,
    })
  );
  scene.add(embers);

  function resize() {
    const w = canvas.clientWidth;
    const h = canvas.clientHeight;
    if (w && h && (canvas.width !== w || canvas.height !== h)) {
      renderer.setSize(w, h, false);
      camera.aspect = w / h;
      camera.updateProjectionMatrix();
    }
  }

  /* 画面内にあるときだけ描画して負荷を抑える */
  let inView = false;
  new IntersectionObserver((entries) => {
    inView = entries[0].isIntersecting;
  }).observe(canvas);

  const mouse = { x: 0, y: 0 };
  window.addEventListener("pointermove", (e) => {
    mouse.x = (e.clientX / window.innerWidth - 0.5) * 2;
    mouse.y = (e.clientY / window.innerHeight - 0.5) * 2;
  });

  const clock = new THREE.Clock();
  renderer.setAnimationLoop(() => {
    if (!inView) return;
    resize();
    const t = clock.getElapsedTime();
    if (!prefersReducedMotion) {
      stars.rotation.y = t * 0.02;
      embers.rotation.y = -t * 0.03;
      embers.position.y = Math.sin(t * 0.4) * 0.8;
      camera.position.x += (mouse.x * 1.2 - camera.position.x) * 0.03;
      camera.position.y += (-mouse.y * 0.8 - camera.position.y) * 0.03;
      camera.lookAt(0, 0, 0);
    }
    renderer.render(scene, camera);
  });
}
