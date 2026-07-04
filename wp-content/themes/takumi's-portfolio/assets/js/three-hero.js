/* ============================================================
   Hero 3D Scene — ワイヤーフレーム富士山 × 星空パーティクル
   Three.js (ES Modules / CDN)
   ============================================================ */
import * as THREE from "https://unpkg.com/three@0.160.0/build/three.module.js";

const canvas = document.getElementById("hero-canvas");

const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

function supportsWebGL() {
  try {
    const c = document.createElement("canvas");
    return !!(window.WebGLRenderingContext && (c.getContext("webgl") || c.getContext("experimental-webgl")));
  } catch (e) {
    return false;
  }
}

if (canvas && supportsWebGL()) {
  init();
}

function init() {
  const renderer = new THREE.WebGLRenderer({
    canvas,
    antialias: true,
    alpha: true,
    powerPreference: "high-performance",
  });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setSize(canvas.clientWidth, canvas.clientHeight, false);

  const scene = new THREE.Scene();
  scene.fog = new THREE.FogExp2(0x070b14, 0.028);

  const camera = new THREE.PerspectiveCamera(
    55,
    canvas.clientWidth / canvas.clientHeight,
    0.1,
    200
  );
  camera.position.set(0, 4.2, 17);
  camera.lookAt(0, 3, 0);

  /* ---------- 富士山ワイヤーフレーム地形 ---------- */
  // 平面を放射状に隆起させて山体を生成する
  const SIZE = 46;
  const SEG = 110;
  const geo = new THREE.PlaneGeometry(SIZE, SIZE, SEG, SEG);
  geo.rotateX(-Math.PI / 2);

  const pos = geo.attributes.position;
  for (let i = 0; i < pos.count; i++) {
    const x = pos.getX(i);
    const z = pos.getZ(i);
    const d = Math.sqrt(x * x + z * z);
    // 山体: 中心からの距離で減衰するプロファイル(裾野が広がる冪カーブ)
    let h = Math.max(0, 8.2 * Math.pow(Math.max(0, 1 - d / 19), 1.55));
    // 山頂を火口らしく少し平らに
    if (h > 6.6) h = 6.6 + (h - 6.6) * 0.25;
    // 尾根の起伏
    h += Math.sin(x * 0.7) * Math.cos(z * 0.55) * 0.28 * Math.min(1, d / 6);
    h += Math.sin(x * 2.1 + z * 1.4) * 0.09;
    pos.setY(i, h);
  }
  geo.computeVertexNormals();

  // 標高に応じたグラデーション(藍 → 白 / 山頂は雪色)
  const colors = new Float32Array(pos.count * 3);
  const cLow = new THREE.Color(0x1c2b52);
  const cMid = new THREE.Color(0x3f5f9e);
  const cTop = new THREE.Color(0xdfe8f5);
  for (let i = 0; i < pos.count; i++) {
    const h = pos.getY(i) / 7.0;
    const c = new THREE.Color();
    if (h < 0.55) c.lerpColors(cLow, cMid, h / 0.55);
    else c.lerpColors(cMid, cTop, (h - 0.55) / 0.45);
    colors[i * 3] = c.r;
    colors[i * 3 + 1] = c.g;
    colors[i * 3 + 2] = c.b;
  }
  geo.setAttribute("color", new THREE.BufferAttribute(colors, 3));

  const mountain = new THREE.Mesh(
    geo,
    new THREE.MeshBasicMaterial({
      vertexColors: true,
      wireframe: true,
      transparent: true,
      opacity: 0.55,
    })
  );
  mountain.position.y = -1.2;
  scene.add(mountain);

  // 山肌の頂点にポイントを重ねて発光感を出す
  const peaks = new THREE.Points(
    geo,
    new THREE.PointsMaterial({
      vertexColors: true,
      size: 0.045,
      transparent: true,
      opacity: 0.7,
      depthWrite: false,
    })
  );
  peaks.position.copy(mountain.position);
  scene.add(peaks);

  /* ---------- 星空パーティクル ---------- */
  const STAR_COUNT = 1400;
  const starGeo = new THREE.BufferGeometry();
  const starPos = new Float32Array(STAR_COUNT * 3);
  const starPhase = new Float32Array(STAR_COUNT);
  for (let i = 0; i < STAR_COUNT; i++) {
    const r = 30 + Math.random() * 60;
    const theta = Math.random() * Math.PI * 2;
    const phi = Math.acos(2 * Math.random() - 1);
    starPos[i * 3] = r * Math.sin(phi) * Math.cos(theta);
    starPos[i * 3 + 1] = Math.abs(r * Math.cos(phi)) * 0.7 + 1;
    starPos[i * 3 + 2] = r * Math.sin(phi) * Math.sin(theta);
    starPhase[i] = Math.random() * Math.PI * 2;
  }
  starGeo.setAttribute("position", new THREE.BufferAttribute(starPos, 3));
  const stars = new THREE.Points(
    starGeo,
    new THREE.PointsMaterial({
      color: 0xbcd0f7,
      size: 0.16,
      transparent: true,
      opacity: 0.85,
      depthWrite: false,
      sizeAttenuation: true,
    })
  );
  scene.add(stars);

  /* ---------- 漂う紅パーティクル(アクセント) ---------- */
  const EMBER_COUNT = 90;
  const emberGeo = new THREE.BufferGeometry();
  const emberPos = new Float32Array(EMBER_COUNT * 3);
  for (let i = 0; i < EMBER_COUNT; i++) {
    emberPos[i * 3] = (Math.random() - 0.5) * 36;
    emberPos[i * 3 + 1] = Math.random() * 12;
    emberPos[i * 3 + 2] = (Math.random() - 0.5) * 36;
  }
  emberGeo.setAttribute("position", new THREE.BufferAttribute(emberPos, 3));
  const embers = new THREE.Points(
    emberGeo,
    new THREE.PointsMaterial({
      color: 0xe94560,
      size: 0.12,
      transparent: true,
      opacity: 0.8,
      depthWrite: false,
      blending: THREE.AdditiveBlending,
    })
  );
  scene.add(embers);

  /* ---------- マウスパララックス ---------- */
  const mouse = { x: 0, y: 0 };
  window.addEventListener("pointermove", (e) => {
    mouse.x = (e.clientX / window.innerWidth - 0.5) * 2;
    mouse.y = (e.clientY / window.innerHeight - 0.5) * 2;
  });

  /* ---------- リサイズ ---------- */
  function resize() {
    const w = canvas.clientWidth;
    const h = canvas.clientHeight;
    if (canvas.width !== w || canvas.height !== h) {
      renderer.setSize(w, h, false);
      camera.aspect = w / h;
      camera.updateProjectionMatrix();
    }
  }
  window.addEventListener("resize", resize);

  /* ---------- ループ ---------- */
  const clock = new THREE.Clock();
  let visible = true;
  document.addEventListener("visibilitychange", () => {
    visible = document.visibilityState === "visible";
  });

  renderer.setAnimationLoop(() => {
    if (!visible) return;
    resize();
    const t = clock.getElapsedTime();

    if (!prefersReducedMotion) {
      mountain.rotation.y = t * 0.045;
      peaks.rotation.y = mountain.rotation.y;
      stars.rotation.y = -t * 0.008;

      // 紅パーティクルをゆっくり上昇させ、上限で下へ戻す
      const ep = embers.geometry.attributes.position;
      for (let i = 0; i < EMBER_COUNT; i++) {
        let y = ep.getY(i) + 0.0045;
        if (y > 13) y = 0;
        ep.setY(i, y);
      }
      ep.needsUpdate = true;
    }

    // カメラのパララックスと呼吸のような揺らぎ
    const targetX = mouse.x * 1.6;
    const targetY = 4.2 - mouse.y * 1.0 + Math.sin(t * 0.5) * 0.15;
    camera.position.x += (targetX - camera.position.x) * 0.03;
    camera.position.y += (targetY - camera.position.y) * 0.03;
    camera.lookAt(0, 3, 0);

    renderer.render(scene, camera);
  });
}
