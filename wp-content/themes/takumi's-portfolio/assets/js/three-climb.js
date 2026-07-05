/* ============================================================
   Climb Mode — スクロールで富士山を登るポートフォリオ体験
   近未来データ空間: シアンに光るワイヤーフレーム富士・バイナリの流れ・
   浮遊するスクリーン。カメラが螺旋状に登り、山頂で光の核に至る
   ============================================================ */
import * as THREE from "https://unpkg.com/three@0.160.0/build/three.module.js";

const canvas = document.getElementById("climb-canvas");
const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

function supportsWebGL() {
  try {
    const c = document.createElement("canvas");
    return !!(window.WebGLRenderingContext && (c.getContext("webgl") || c.getContext("experimental-webgl")));
  } catch (e) {
    return false;
  }
}

/* ---------- 合目ナビゲーション(WebGL不可でも動作) ---------- */
const stations = [...document.querySelectorAll(".climb-station")];
const navItems = [...document.querySelectorAll(".climb-nav a")];
let activeStation = 0; // リーダーラインとプルダウンが参照する現在地
if (stations.length) {
  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const idx = stations.indexOf(entry.target);
        activeStation = idx;
        stations.forEach((st, i) => st.classList.toggle("is-active", i === idx));
        navItems.forEach((a, i) => a.classList.toggle("is-active", i === idx));
      });
    },
    { threshold: 0.35 }
  );
  stations.forEach((s) => io.observe(s));
  navItems.forEach((a, i) =>
    a.addEventListener("click", (e) => {
      e.preventDefault();
      stations[i]?.scrollIntoView({ behavior: "smooth" });
    })
  );
}

if (canvas && supportsWebGL()) {
  init();
}

function init() {
  const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, powerPreference: "high-performance" });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

  const scene = new THREE.Scene();
  scene.fog = new THREE.FogExp2(0x070b14, 0.02);

  const camera = new THREE.PerspectiveCamera(55, 1, 0.1, 300);

  /* ---------- 富士山(ワイヤーフレーム) ---------- */
  const SIZE = 46;
  const SEG = 110;
  const geo = new THREE.PlaneGeometry(SIZE, SIZE, SEG, SEG);
  geo.rotateX(-Math.PI / 2);
  const pos = geo.attributes.position;
  function shapeHeight(x, z) {
    const d = Math.sqrt(x * x + z * z);
    let h = Math.max(0, 8.2 * Math.pow(Math.max(0, 1 - d / 19), 1.55));
    if (h > 6.6) h = 6.6 + (h - 6.6) * 0.25;
    h += Math.sin(x * 0.7) * Math.cos(z * 0.55) * 0.28 * Math.min(1, d / 6);
    h += Math.sin(x * 2.1 + z * 1.4) * 0.09;
    return h;
  }
  for (let i = 0; i < pos.count; i++) {
    pos.setY(i, shapeHeight(pos.getX(i), pos.getZ(i)));
  }
  geo.computeVertexNormals();

  const colors = new Float32Array(pos.count * 3);
  const cLow = new THREE.Color(0x0e3350);
  const cMid = new THREE.Color(0x1e7fb8);
  const cTop = new THREE.Color(0xbdf0ff);
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

  // 不可視の山体(深度のみ書き込む): ルートや奥側の線が地形で正しく隠れる
  const occluder = new THREE.Mesh(
    geo,
    new THREE.MeshBasicMaterial({ colorWrite: false, polygonOffset: true, polygonOffsetFactor: 2, polygonOffsetUnits: 2 })
  );
  occluder.position.y = -1.2;
  scene.add(occluder);

  const mountain = new THREE.Mesh(
    geo,
    new THREE.MeshBasicMaterial({ vertexColors: true, wireframe: true, transparent: true, opacity: 0.55 })
  );
  mountain.position.y = -1.2;
  scene.add(mountain);

  const peaks = new THREE.Points(
    geo,
    new THREE.PointsMaterial({ vertexColors: true, size: 0.045, transparent: true, opacity: 0.7, depthWrite: false })
  );
  peaks.position.copy(mountain.position);
  scene.add(peaks);

  /* ---------- 赤い登山ルート(麓 → 山頂) ---------- */
  function routePoint(t) {
    // カメラの旋回(-π/2 + t·2.2π)より少しだけ先行した、見えている斜面を通す
    const ang = -Math.PI / 2 + t * Math.PI * 2.2 + Math.PI * 0.3;
    const r = 16.5 * Math.pow(1 - t, 0.92);
    const x = Math.cos(ang) * r;
    const z = Math.sin(ang) * r;
    return new THREE.Vector3(x, shapeHeight(x, z) - 1.2 + 0.12, z);
  }
  // 補間で地形にめり込まないよう、カーブ上のどの点でも地形の高さを直接計算する
  class RoutePath extends THREE.Curve {
    getPoint(t, target = new THREE.Vector3()) {
      return target.copy(routePoint(t));
    }
  }
  const routeGeo = new THREE.TubeGeometry(new RoutePath(), 900, 0.05, 6, false);
  const route = new THREE.Mesh(routeGeo, new THREE.MeshBasicMaterial({ color: 0xe94560, fog: false }));
  scene.add(route);
  const routeIndexCount = routeGeo.index.count;
  routeGeo.setDrawRange(0, 0);

  /* ---------- 星空 ---------- */
  const STAR_COUNT = 1200;
  const starGeo = new THREE.BufferGeometry();
  const starPos = new Float32Array(STAR_COUNT * 3);
  for (let i = 0; i < STAR_COUNT; i++) {
    const r = 40 + Math.random() * 80;
    const theta = Math.random() * Math.PI * 2;
    const phi = Math.acos(2 * Math.random() - 1);
    starPos[i * 3] = r * Math.sin(phi) * Math.cos(theta);
    starPos[i * 3 + 1] = Math.abs(r * Math.cos(phi)) * 0.8 + 1;
    starPos[i * 3 + 2] = r * Math.sin(phi) * Math.sin(theta);
  }
  starGeo.setAttribute("position", new THREE.BufferAttribute(starPos, 3));
  const starMat = new THREE.PointsMaterial({
    color: 0xa9e4ff,
    size: 0.18,
    transparent: true,
    opacity: 0.85,
    depthWrite: false,
  });
  const stars = new THREE.Points(starGeo, starMat);
  scene.add(stars);

  /* ---------- グローテクスチャ(光の核・ボケ光で共用) ---------- */
  function makeGlowTexture(inner, mid) {
    const c = document.createElement("canvas");
    c.width = c.height = 128;
    const ctx = c.getContext("2d");
    const g = ctx.createRadialGradient(64, 64, 0, 64, 64, 64);
    g.addColorStop(0, inner);
    g.addColorStop(0.35, mid);
    g.addColorStop(1, "rgba(0, 0, 0, 0)");
    ctx.fillStyle = g;
    ctx.fillRect(0, 0, 128, 128);
    return new THREE.CanvasTexture(c);
  }

  /* ---------- 光の核(山頂で現れる) ---------- */
  const sunMat = new THREE.SpriteMaterial({
    map: makeGlowTexture("rgba(255, 255, 255, 1)", "rgba(140, 220, 255, 0.85)"),
    transparent: true,
    opacity: 0,
    depthWrite: false,
    blending: THREE.AdditiveBlending,
  });
  const sun = new THREE.Sprite(sunMat);
  sun.scale.set(34, 34, 1);
  scene.add(sun);

  /* ---------- バイナリ(0/1)の流れ ---------- */
  function makeCharTexture(ch) {
    const c = document.createElement("canvas");
    c.width = c.height = 64;
    const ctx = c.getContext("2d");
    ctx.font = "bold 46px 'Courier New', monospace";
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillStyle = "#bfe9ff";
    ctx.fillText(ch, 32, 34);
    return new THREE.CanvasTexture(c);
  }
  function makeBinaryStream(ch, count) {
    const g = new THREE.BufferGeometry();
    const p = new Float32Array(count * 3);
    for (let i = 0; i < count; i++) {
      const ang = Math.random() * Math.PI * 2;
      const r = 20 + Math.random() * 26;
      p[i * 3] = Math.cos(ang) * r;
      p[i * 3 + 1] = Math.random() * 14 - 1;
      p[i * 3 + 2] = Math.sin(ang) * r;
    }
    g.setAttribute("position", new THREE.BufferAttribute(p, 3));
    return new THREE.Points(
      g,
      new THREE.PointsMaterial({
        map: makeCharTexture(ch),
        color: 0x9fdcff,
        size: 0.55,
        transparent: true,
        opacity: 0.4,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
      })
    );
  }
  const binary0 = makeBinaryStream("0", 220);
  const binary1 = makeBinaryStream("1", 220);
  scene.add(binary0, binary1);

  /* ---------- ボケ光(ふわっと漂う光の玉) ---------- */
  function makeBokeh(count, size, opacity) {
    const g = new THREE.BufferGeometry();
    const p = new Float32Array(count * 3);
    for (let i = 0; i < count; i++) {
      const ang = Math.random() * Math.PI * 2;
      const r = 12 + Math.random() * 34;
      p[i * 3] = Math.cos(ang) * r;
      p[i * 3 + 1] = Math.random() * 13 - 1;
      p[i * 3 + 2] = Math.sin(ang) * r;
    }
    g.setAttribute("position", new THREE.BufferAttribute(p, 3));
    return new THREE.Points(
      g,
      new THREE.PointsMaterial({
        map: makeGlowTexture("rgba(160, 225, 255, 0.9)", "rgba(80, 170, 230, 0.35)"),
        color: 0xffffff,
        size,
        transparent: true,
        opacity,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
      })
    );
  }
  const bokehSmall = makeBokeh(60, 0.9, 0.35);
  const bokehLarge = makeBokeh(24, 2.4, 0.18);
  scene.add(bokehSmall, bokehLarge);

  /* ---------- 浮遊するスクリーン(制作物のパネル) ---------- */
  const PANEL_SRCS = [
    "img/YLMEMORIA/YL MEMORIA.png",
    "img/wakasyatiya/若鯱家-top.png",
    "img/hikariwo/HiKaRiWo_LP.png",
    "img/img/portfolio.png",
    "img/Lapesca/Lapeseca_top.png",
    "img/TODO/todo-top.jpeg",
  ];
  const BASE3D = window.TAKUMI_BASE || "";
  const texLoader = new THREE.TextureLoader();
  const panels = PANEL_SRCS.map((src, i) => {
    const tP = 0.58 + i * 0.055; // Workセクション(進行度0.75)前後に散らす
    const base = routePoint(tP);
    const dir = new THREE.Vector3(base.x, 0, base.z).normalize();
    const posV = base
      .clone()
      .addScaledVector(dir, 3.2 + (i % 3) * 1.4)
      .add(new THREE.Vector3(0, 1.4 + (i % 2) * 1.8, 0));
    const tex = texLoader.load(BASE3D + src);
    tex.colorSpace = THREE.SRGBColorSpace;
    const mesh = new THREE.Mesh(
      new THREE.PlaneGeometry(3.2, 2.0),
      new THREE.MeshBasicMaterial({ map: tex, transparent: true, opacity: 0, side: THREE.DoubleSide, depthWrite: false })
    );
    mesh.position.copy(posV);
    mesh.visible = false;
    scene.add(mesh);
    return { mesh, tP, phase: i * 1.7, tilt: (i % 2 ? 1 : -1) * 0.28, baseY: posV.y };
  });

  /* ---------- スキル吹き出し(山道沿いに配置) ---------- */
  const SKILLS = window.TAKUMI_SKILLS || [];
  const SKILL_ICON_BASE = window.TAKUMI_SKILL_ICON_BASE || "";

  function makeSkillBubbleTexture(iconImg) {
    const c = document.createElement("canvas");
    c.width = 180;
    c.height = 210;
    const ctx = c.getContext("2d");
    const r = 20;
    const w = c.width;
    const h = 180; // 吹き出し本体(正方形寄り)の高さ。残りは尻尾。
    ctx.fillStyle = "rgba(10, 16, 30, 0.88)";
    ctx.strokeStyle = "rgba(233, 69, 96, 0.6)";
    ctx.lineWidth = 3;
    ctx.beginPath();
    ctx.moveTo(r, 0);
    ctx.arcTo(w, 0, w, h, r);
    ctx.arcTo(w, h, 0, h, r);
    ctx.arcTo(0, h, 0, 0, r);
    ctx.arcTo(0, 0, w, 0, r);
    ctx.lineTo(w * 0.58, h);
    ctx.lineTo(w * 0.42, h + 28);
    ctx.lineTo(w * 0.34, h);
    ctx.closePath();
    ctx.fill();
    ctx.stroke();

    if (iconImg) {
      const size = 108;
      ctx.drawImage(iconImg, (w - size) / 2, (h - size) / 2, size, size);
    }

    const tex = new THREE.CanvasTexture(c);
    tex.colorSpace = THREE.SRGBColorSpace;
    return tex;
  }

  const skillBubbles = SKILLS.map((skill, i) => {
    const n = SKILLS.length;
    const tP = 0.36 + (i / Math.max(1, n - 1)) * 0.28; // Skillステーション(0.5)周辺に分散
    const base = routePoint(tP);
    const dir = new THREE.Vector3(base.x, 0, base.z).normalize();
    const side = i % 2 ? 1 : -1;
    const posV = base
      .clone()
      .addScaledVector(dir, 2.6 + (i % 3) * 1.1)
      .add(new THREE.Vector3(side * 0.6, 1.6 + (i % 2) * 1.4, side * 0.6));

    const mesh = new THREE.Mesh(
      new THREE.PlaneGeometry(1.7, 2.0),
      new THREE.MeshBasicMaterial({ transparent: true, opacity: 0, side: THREE.DoubleSide, depthWrite: false })
    );
    mesh.position.copy(posV);
    mesh.visible = false;
    scene.add(mesh);

    // アイコンはローカル配置のSVGのみ読み込む(外部ドメイン経由だとCanvasが汚染されテクスチャ化できないため)
    const img = new Image();
    const applyTexture = (loadedImg) => {
      mesh.material.map = makeSkillBubbleTexture(loadedImg);
      mesh.material.needsUpdate = true;
    };
    img.onload = () => applyTexture(img);
    img.onerror = () => applyTexture(null);
    img.src = SKILL_ICON_BASE + skill.icon + ".svg";

    return { mesh, tP, phase: i * 2.1, baseY: posV.y };
  });

  /* ---------- 空の色(夜 → 夜明け) ---------- */
  const skyStops = [
    { p: 0.0, c: new THREE.Color(0x050a16) },
    { p: 0.45, c: new THREE.Color(0x081428) },
    { p: 0.75, c: new THREE.Color(0x0d2240) },
    { p: 1.0, c: new THREE.Color(0x143a63) },
  ];
  const skyColor = new THREE.Color();
  function updateSky(p) {
    for (let i = 0; i < skyStops.length - 1; i++) {
      const a = skyStops[i];
      const b = skyStops[i + 1];
      if (p >= a.p && p <= b.p) {
        skyColor.lerpColors(a.c, b.c, (p - a.p) / (b.p - a.p));
        break;
      }
    }
    scene.background = skyColor;
    scene.fog.color.copy(skyColor);
  }

  /* ---------- スクロール進行度 ---------- */
  let target = 0;
  let prog = 0;
  function readScroll() {
    const max = document.documentElement.scrollHeight - window.innerHeight;
    target = max > 0 ? Math.min(1, Math.max(0, window.scrollY / max)) : 0;
  }
  window.addEventListener("scroll", readScroll, { passive: true });
  readScroll();

  const mouse = { x: 0 };
  window.addEventListener("pointermove", (e) => {
    mouse.x = (e.clientX / window.innerWidth - 0.5) * 2;
  });

  function resize() {
    const w = canvas.clientWidth;
    const h = canvas.clientHeight;
    if (canvas.width !== w || canvas.height !== h) {
      renderer.setSize(w, h, false);
      camera.aspect = w / h;
      camera.updateProjectionMatrix();
    }
  }

  const smoothstep = (a, b, x) => {
    const t = Math.min(1, Math.max(0, (x - a) / (b - a)));
    return t * t * (3 - 2 * t);
  };

  const clock = new THREE.Clock();
  renderer.setAnimationLoop(() => {
    resize();
    const t = clock.getElapsedTime();

    prog += (target - prog) * (prefersReducedMotion ? 1 : 0.06);
    const p = prog;

    // 螺旋状の登山カメラ: 麓(遠く低い) → 山頂(近く高い)
    const angle = -Math.PI / 2 + p * Math.PI * 2.2 + mouse.x * 0.08;
    const radius = 18 - p * 11.5;
    const camY = 1.6 + p * 7.4;
    camera.position.set(Math.cos(angle) * radius, camY, Math.sin(angle) * radius);
    camera.lookAt(0, 2.2 + p * 3.0, 0);

    // 赤い登山ルートが進行度に合わせて山頂まで伸びていく
    const reveal = Math.min(1, p * 1.05 + 0.04);
    routeGeo.setDrawRange(0, Math.floor((routeIndexCount * reveal) / 3) * 3);

    // 山頂に近づくほど夜が明ける
    updateSky(p);
    starMat.opacity = 0.85 * (1 - smoothstep(0.55, 0.95, p));

    // 光の核: カメラの反対側(山頂の向こう)から現れる
    const sunAngle = angle + Math.PI;
    const sunY = -8 + smoothstep(0.5, 1, p) * 22;
    sun.position.set(Math.cos(sunAngle) * 70, sunY, Math.sin(sunAngle) * 70);
    sunMat.opacity = smoothstep(0.58, 0.9, p);

    // 浮遊スクリーン: Workセクションに近づくと現れ、ゆっくり漂う
    panels.forEach(({ mesh, tP, phase, tilt, baseY }) => {
      const prox = Math.max(0, 1 - Math.abs(p - tP) / 0.16);
      mesh.material.opacity = prox * prox * 0.95;
      mesh.visible = mesh.material.opacity > 0.02;
      if (mesh.visible) {
        mesh.position.y = baseY + Math.sin(t * 0.8 + phase) * 0.18;
        mesh.lookAt(camera.position);
        mesh.rotateY(tilt);
      }
    });

    // スキル吹き出し: Skillセクションに近づくと現れ、ゆっくり漂う
    skillBubbles.forEach(({ mesh, tP, phase, baseY }) => {
      const prox = Math.max(0, 1 - Math.abs(p - tP) / 0.14);
      const eased = prox * prox;
      mesh.material.opacity = eased * 0.95;
      mesh.visible = mesh.material.opacity > 0.02 && !!mesh.material.map;
      if (mesh.visible) {
        mesh.position.y = baseY + Math.sin(t * 0.9 + phase) * 0.15;
        mesh.lookAt(camera.position);
      }
    });

    if (!prefersReducedMotion) {
      stars.rotation.y = -t * 0.006;
      binary0.rotation.y = t * 0.012;
      binary1.rotation.y = -t * 0.009;
      binary0.position.y = Math.sin(t * 0.25) * 0.6;
      binary1.position.y = Math.cos(t * 0.2) * 0.6;
      bokehSmall.rotation.y = -t * 0.006;
      bokehLarge.rotation.y = t * 0.004;
    }

    renderer.render(scene, camera);
  });
}
