import * as T from 'three';
import { dimensions, priceText, roomOf } from './data.js';
import { BRASS, ENTRANCES, PASSAGE, SILVER, WALL, brassPlacement, canWalk, layoutSilver, walkSpeedAt, zoneAt } from './layout.js';

// Default look of the silver room; config.silverTheme can override any key.
const SILVER_THEME = { wallColor: '#ece8e1', floorColor: '#efdcc0', ceilingColor: '#f1ede6', glow: '#fff1d6' };

export async function createRoom(host, onReady, assetsBase, config, startEntrance = 'brass') {
  const artworks = config.artworks;
  const theme = config.theme || {};
  const silverTheme = { ...SILVER_THEME, ...(config.silverTheme || {}) };
  const scene = new T.Scene();
  scene.background = new T.Color(theme.wallColor || '#d9cfba');
  const camera = new T.PerspectiveCamera(60, host.clientWidth / host.clientHeight, 0.06, 45);
  const pose = ENTRANCES[startEntrance] || ENTRANCES.brass;
  camera.position.set(...pose.position);
  camera.lookAt(...pose.lookAt);

  // Phones and tablets get a lighter renderer: no MSAA, a lower pixel ratio and no shadow maps.
  // Six shadow-casting spotlights exceed what some mobile GPUs (notably iPhone Safari) can compile,
  // which left the 3D room blank. Desktop keeps the full-quality shadows.
  const touchDevice = matchMedia('(pointer:coarse)').matches || /iPhone|iPad|iPod|Android/i.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
  const renderer = new T.WebGLRenderer({ antialias: !touchDevice, powerPreference: touchDevice ? 'default' : 'high-performance', precision: 'highp' });
  let shaderFailed = false;
  renderer.debug.checkShaderErrors = true;
  renderer.debug.onShaderError = () => { shaderFailed = true; };
  renderer.setPixelRatio(Math.min(devicePixelRatio, touchDevice ? 1.5 : 1.6));
  renderer.setSize(host.clientWidth, host.clientHeight, false);
  renderer.outputColorSpace = T.SRGBColorSpace;
  renderer.toneMapping = T.ACESFilmicToneMapping;
  renderer.toneMappingExposure = theme.toneMappingExposure ?? 1.05;
  renderer.shadowMap.enabled = !touchDevice;
  renderer.shadowMap.type = T.PCFShadowMap;
  host.appendChild(renderer.domElement);
  const maxAnisotropy = Math.min(8, renderer.capabilities.getMaxAnisotropy());

  const textures = [];
  const rayTargets = [];
  const labels = [];
  const imageUrl = (path) => {
    const url = new URL(path, assetsBase + '/');
    if (url.origin !== window.location.origin) throw new Error('Cross-origin artwork image blocked');
    return url.href;
  };

  function box(w, h, d, x, y, z, color, roughness = 0.8) {
    const m = new T.Mesh(new T.BoxGeometry(w, h, d), new T.MeshStandardMaterial({ color, roughness }));
    m.position.set(x, y, z);
    m.receiveShadow = true;
    m.castShadow = true;
    scene.add(m);
    return m;
  }
  // A wall along x (at z) from x0 to x1 with an optional door opening.
  function wallX(x0, x1, z, height, color, door, thickness = WALL) {
    if (!door) return box(x1 - x0, height, thickness, (x0 + x1) / 2, height / 2, z, color);
    box(door.x0 - x0, height, thickness, (x0 + door.x0) / 2, height / 2, z, color);
    box(x1 - door.x1, height, thickness, (door.x1 + x1) / 2, height / 2, z, color);
    box(door.x1 - door.x0, height - door.height, thickness, (door.x0 + door.x1) / 2, (height + door.height) / 2, z, color);
  }
  function wallZ(z0, z1, x, height, color) { box(WALL, height, z1 - z0, x, height / 2, (z0 + z1) / 2, color); }

  // ── Wood texture shared by every floor ────────────────────────────────────────────────
  const canvas = document.createElement('canvas');
  canvas.width = 128; canvas.height = 512;
  const ctx = canvas.getContext('2d');
  ctx.fillStyle = '#a18765'; ctx.fillRect(0, 0, 128, 512);
  let seed = 42;
  const rand = () => { seed = (seed * 1664525 + 1013904223) >>> 0; return seed / 4294967296; };
  for (let i = 0; i < 280; i++) {
    ctx.strokeStyle = `rgba(${rand() > 0.5 ? '63,39,18' : '223,200,158'},${rand() * 0.12})`;
    ctx.lineWidth = rand() * 2; ctx.beginPath();
    const x = rand() * 128; ctx.moveTo(x, 0); ctx.bezierCurveTo(x + rand() * 6, 160, x - rand() * 8, 340, x + rand() * 4, 512); ctx.stroke();
  }
  ctx.fillStyle = 'rgba(40,24,10,.35)'; ctx.fillRect(0, 0, 2, 512);
  const wood = new T.CanvasTexture(canvas); wood.colorSpace = T.SRGBColorSpace; textures.push(wood);

  // ── Brass room ────────────────────────────────────────────────────────────────────────
  const wall = theme.wallColor || '#e7dfcd';
  const bw = BRASS.x1 - BRASS.x0, bd = BRASS.z1 - BRASS.z0, bcx = (BRASS.x0 + BRASS.x1) / 2, bcz = (BRASS.z0 + BRASS.z1) / 2;
  wallX(BRASS.x0 - WALL / 2, BRASS.x1 + WALL / 2, BRASS.z0, BRASS.height, wall);
  wallZ(BRASS.z0, BRASS.z1, BRASS.x0, BRASS.height, wall);
  wallZ(BRASS.z0, BRASS.z1, BRASS.x1, BRASS.height, wall);
  // Shared wall with the silver hall: the brass-coloured half faces this room, the hall builds the other half.
  wallX(BRASS.x0 - WALL / 2, BRASS.x1 + WALL / 2, BRASS.z1 - WALL / 4, BRASS.height, wall, PASSAGE, WALL / 2);
  box(bw, 0.1, bd, bcx, BRASS.height + 0.03, bcz, theme.ceilingColor || '#ece5d6');
  box(bw, 0.08, bd, bcx, -0.05, bcz, theme.floorColor || '#786349');
  // Planks: one mesh per row of boards (few draw calls), each board a slightly different tone.
  {
    const cols = Math.round(bw / 0.2), rows = Math.ceil(bd / 1.125), pw = bw / cols, pd = bd / rows;
    const plank = new T.BoxGeometry(pw - 0.002, 0.018, pd - 0.005);
    const mat = new T.MeshStandardMaterial({ map: wood, roughness: 0.56 });
    const boards = new T.InstancedMesh(plank, mat, cols * rows); boards.receiveShadow = true;
    const m4 = new T.Matrix4(), tone = new T.Color();
    for (let i = 0, n = 0; i < cols; i++) for (let j = 0; j < rows; j++, n++) {
      m4.makeTranslation(BRASS.x0 + (i + 0.5) * pw, 0, BRASS.z0 + (j + 0.5) * pd);
      boards.setMatrixAt(n, m4); boards.setColorAt(n, tone.setHSL(0.095, 0.22, 0.69 + rand() * 0.07));
    }
    scene.add(boards);
  }
  box(bw - WALL, 0.065, 0.022, bcx, 0.04, BRASS.z0 + WALL / 2 + 0.011, '#9b825b');
  box(0.022, 0.065, bd - WALL, BRASS.x0 + WALL / 2 + 0.011, 0.04, bcz, '#9b825b');
  box(0.022, 0.065, bd - WALL, BRASS.x1 - WALL / 2 - 0.011, 0.04, bcz, '#9b825b');

  // ── Silver hall: one open hall joined to the brass room by a pass-through ─────────────
  function floorPlane(r, color, repeatX, repeatZ) {
    const map = wood.clone(); map.wrapS = map.wrapT = T.RepeatWrapping; map.repeat.set(repeatX, repeatZ); map.needsUpdate = true; textures.push(map);
    const m = new T.Mesh(new T.PlaneGeometry(r.x1 - r.x0, r.z1 - r.z0), new T.MeshStandardMaterial({ color, map, roughness: 0.55 }));
    m.rotation.x = -Math.PI / 2; m.position.set((r.x0 + r.x1) / 2, 0.009, (r.z0 + r.z1) / 2); m.receiveShadow = true; scene.add(m);
  }
  const sw = silverTheme.wallColor;
  floorPlane(SILVER, silverTheme.floorColor, (SILVER.x1 - SILVER.x0) / 0.2, (SILVER.z1 - SILVER.z0) / 1.12);
  box(SILVER.x1 - SILVER.x0, 0.1, SILVER.z1 - SILVER.z0, (SILVER.x0 + SILVER.x1) / 2, SILVER.height + 0.05, (SILVER.z0 + SILVER.z1) / 2, silverTheme.ceilingColor);
  wallX(SILVER.x0 - WALL / 2, SILVER.x1 + WALL / 2, SILVER.z1, SILVER.height, sw);
  wallZ(SILVER.z0, SILVER.z1, SILVER.x0, SILVER.height, sw);
  wallZ(SILVER.z0, SILVER.z1, SILVER.x1, SILVER.height, sw);
  wallX(SILVER.x0 - WALL / 2, SILVER.x1 + WALL / 2, SILVER.z0 + WALL / 4, SILVER.height, sw, PASSAGE, WALL / 2);
  // Skirting and a slim picture rail give the long walls a finished line.
  for (const [x, len, rot] of [[SILVER.x0 + 0.08, SILVER.z1 - SILVER.z0, true], [SILVER.x1 - 0.08, SILVER.z1 - SILVER.z0, true]]) {
    box(0.022, 0.065, len - WALL, x, 0.04, (SILVER.z0 + SILVER.z1) / 2, '#9b825b');
    box(0.018, 0.022, len - WALL, x, 2.95, (SILVER.z0 + SILVER.z1) / 2, '#b9a27a', 0.4);
  }
  box(SILVER.x1 - SILVER.x0 - WALL, 0.065, 0.022, 0, 0.04, SILVER.z1 - 0.08, '#9b825b');
  box(SILVER.x1 - SILVER.x0 - WALL, 0.022, 0.018, 0, 2.95, SILVER.z1 - 0.08, '#b9a27a', 0.4);

  // The pass-through: a deep trimmed portal with an entrance sign on each side.
  // Parts never share a face with the wall (shared faces flicker as white stripes on phones):
  // linings sit just inside the opening, casings stand 5 mm proud of each wall face.
  const portal = (color) => {
    const m = (w, h, d, x, y, z) => { const b = box(w, h, d, x, y, z, color, 0.35); b.material.metalness = 0.55; return b; };
    const z = BRASS.z1, lining = 0.07, casing = 0.1, proud = 0.018;
    const inner0 = PASSAGE.x0 + 0.001, inner1 = PASSAGE.x1 - 0.001, top = PASSAGE.height - 0.001;
    m(lining, top, WALL + 0.02, inner0 + lining / 2, top / 2, z);
    m(lining, top, WALL + 0.02, inner1 - lining / 2, top / 2, z);
    m(inner1 - inner0, lining, WALL + 0.02, 0, top - lining / 2, z);
    for (const face of [z - WALL / 2 - 0.005 - proud / 2, z + WALL / 2 + 0.005 + proud / 2]) {
      m(casing, PASSAGE.height + casing, proud, PASSAGE.x0 - casing / 2 + 0.02, (PASSAGE.height + casing) / 2, face);
      m(casing, PASSAGE.height + casing, proud, PASSAGE.x1 + casing / 2 - 0.02, (PASSAGE.height + casing) / 2, face);
      m(PASSAGE.x1 - PASSAGE.x0 + 2 * casing - 0.04, casing, proud, 0, PASSAGE.height + casing / 2, face);
    }
  };
  portal('#a88a55');
  function signTexture(kind) {
    const c = document.createElement('canvas'); c.width = 1024; c.height = 220; const t = c.getContext('2d');
    const silver = kind === 'silver';
    t.fillStyle = silver ? '#23252a' : '#2a2014'; t.fillRect(0, 0, 1024, 220);
    t.strokeStyle = silver ? '#cfd2d6' : '#c9a35f'; t.lineWidth = 4; t.strokeRect(12, 12, 1000, 196);
    t.fillStyle = silver ? '#eef0f2' : '#f0d9a6'; t.textAlign = 'center';
    t.font = '600 60px Georgia, serif'; t.fillText(silver ? 'SILVER ENTRANCE' : 'BRASS ENTRANCE', 512, 98);
    t.font = '46px "Noto Sans Myanmar","Myanmar Sangam MN",sans-serif'; t.fillText(silver ? 'ငွေပန်းချီ ခန်းမ' : 'ကြေးဝါပန်းချီ ပြခန်း', 512, 172);
    const tex = new T.CanvasTexture(c); tex.colorSpace = T.SRGBColorSpace; tex.anisotropy = maxAnisotropy; textures.push(tex); return tex;
  }
  // From the brass room the portal leads to the silver hall, and the other way round.
  const brassSide = new T.Mesh(new T.PlaneGeometry(1.25, 0.27), new T.MeshBasicMaterial({ map: signTexture('silver') }));
  brassSide.position.set(0, PASSAGE.height + 0.24, BRASS.z1 - WALL / 2 - 0.012); brassSide.rotation.y = Math.PI; scene.add(brassSide);
  const hallSide = new T.Mesh(new T.PlaneGeometry(1.6, 0.34), new T.MeshBasicMaterial({ map: signTexture('brass') }));
  hallSide.position.set(0, PASSAGE.height + 0.34, BRASS.z1 + WALL / 2 + 0.012); scene.add(hallSide);

  scene.add(new T.HemisphereLight('#fff2d8', '#9c846c', theme.hemisphereIntensity ?? 1.6));
  scene.add(new T.AmbientLight('#f5e6cc', theme.ambientIntensity ?? 0.5));

  // ── Brass artworks: individual spotlights, loaded before the room opens ───────────────
  // compact labels (silver room) use larger type because the plaque is smaller and viewed closer.
  function labelTexture(index, language = 'en', size = 1024) {
    const a = artworks[index];
    const k = size / 1024, compact = size < 1024;
    const c = document.createElement('canvas'); c.width = size; c.height = 384 * k; const t = c.getContext('2d');
    t.scale(k, k);
    t.fillStyle = '#eee8da'; t.fillRect(0, 0, 1024, 384); t.fillStyle = '#a38148'; t.fillRect(36, 40, 48, 4);
    const my = '"Noto Sans Myanmar","Myanmar Sangam MN",sans-serif';
    t.fillStyle = '#333128'; t.font = language === 'my' ? `${compact ? 70 : 46}px ${my}` : `${compact ? 70 : 46}px Georgia`; t.fillText(language === 'my' ? a.my : a.en, 36, compact ? 140 : 130);
    t.font = `${compact ? 44 : 30}px sans-serif`; t.fillStyle = '#706858'; t.fillText(dimensions(a) + '  ·  ' + (language === 'my' ? 'ဘောင်အပါအဝင်' : 'FRAMED'), 36, compact ? 232 : 210);
    t.fillStyle = '#4e4638'; t.font = `${compact ? 52 : 34}px sans-serif`; t.fillText(priceText(a, language), 36, compact ? 330 : 300);
    const texture = new T.CanvasTexture(c); texture.colorSpace = T.SRGBColorSpace; texture.anisotropy = maxAnisotropy; return texture;
  }
  let language = 'my';
  let disposed = false;
  // A broad, indirect ceiling wash keeps the room cozy and the corners readable.
  // The brass room is lit by a few hidden warm lights and a soft wash behind every piece
  // (one spotlight per artwork does not scale past a handful of pieces, especially on phones).
  const bounce = new T.PointLight('#ffe6b8', theme.bounceIntensity ?? 5, 8, 2); bounce.position.set(0, 2.4, 1.2); scene.add(bounce);
  for (const [x, z] of [[-1.45, -2.3], [1.45, -2.3], [-1.45, 0.4], [1.45, 0.4]]) {
    const warm = new T.PointLight(theme.spotlightColor || '#ffe4b2', theme.brassWashIntensity ?? 3.4, 5.5, 1.4); warm.position.set(x, 2.45, z); scene.add(warm);
  }
  // Gallery wash for the silver hall: a few broad lights down the centre instead of one per artwork.
  for (const z of [6.5, 13, 19.5, 26, 32]) {
    const wash = new T.PointLight('#fff1dc', silverTheme.washIntensity ?? 7.5, 13, 1.25); wash.position.set(0, SILVER.height - 0.35, z); scene.add(wash);
  }

  // ── Silver artworks: true physical size, auto-hung, textures streamed by distance ─────
  // A soft wall-wash behind each piece replaces per-artwork spotlights (cheap on phones).
  // The soft glow is computed pixel by pixel. A canvas gradient is not used because Safari dithers
  // canvas gradients with coloured noise, which showed up on iPhone as coloured speckles on the wall.
  const GLOW = 128, glowData = new Uint8Array(GLOW * GLOW * 4);
  for (let y = 0; y < GLOW; y++) for (let x = 0; x < GLOW; x++) {
    const d = Math.min(1, Math.hypot(x + 0.5 - GLOW / 2, y + 0.5 - GLOW / 2) / (GLOW / 2));
    const a = d < 0.55 ? 1 - (d / 0.55) * 0.65 : 0.35 * (1 - (d - 0.55) / 0.45);
    const k = (y * GLOW + x) * 4; glowData[k] = glowData[k + 1] = glowData[k + 2] = 255; glowData[k + 3] = Math.round(255 * a);
  }
  const glowTex = new T.DataTexture(glowData, GLOW, GLOW, T.RGBAFormat);
  glowTex.magFilter = T.LinearFilter; glowTex.minFilter = T.LinearMipmapLinearFilter; glowTex.generateMipmaps = true; glowTex.needsUpdate = true; textures.push(glowTex);
  const glowMat = new T.MeshBasicMaterial({ map: glowTex, color: silverTheme.glow, transparent: true, opacity: 0.26, blending: T.AdditiveBlending, depthWrite: false, polygonOffset: true, polygonOffsetFactor: -2, polygonOffsetUnits: -4 });
  const backMat = new T.MeshStandardMaterial({ color: '#3a2a18', roughness: 0.6 });
  const silverItems = artworks.map((a, index) => ({ ...a, index })).filter((a) => roomOf(a) === 'silver');
  const layout = layoutSilver(silverItems);
  // Group titles on the wall (both languages) with one soft wash of light over each group.
  function titleTexture(my, en) {
    const c = document.createElement('canvas'); c.width = 1024; c.height = 256; const t = c.getContext('2d');
    t.textAlign = 'center'; t.fillStyle = '#7c5d27';
    t.font = '78px "Noto Sans Myanmar","Myanmar Sangam MN",sans-serif'; t.fillText(my, 512, 112);
    t.fillStyle = '#9b7a3e'; t.font = '600 34px Georgia, serif';
    const label = String(en).toUpperCase().split('').join(String.fromCharCode(8202));
    t.fillText(label, 512, 186);
    t.fillStyle = '#b8995e'; t.fillRect(452, 214, 120, 3);
    const tex = new T.CanvasTexture(c); tex.colorSpace = T.SRGBColorSpace; tex.anisotropy = maxAnisotropy; textures.push(tex); return tex;
  }
  for (const title of layout.titles) {
    const plate = new T.Mesh(new T.PlaneGeometry(1.5, 0.375), new T.MeshBasicMaterial({ map: titleTexture(title.my, title.en), transparent: true, depthWrite: false }));
    plate.position.set(title.x, title.y, title.z); plate.rotation.y = title.rotationY; plate.translateZ(0.018); scene.add(plate);
    const wash = new T.Mesh(new T.PlaneGeometry(title.span + 0.9, 2.3), glowMat);
    wash.position.set(title.x, 1.62, title.z); // 2.5 cm off the wall: closer than that, phone GPUs mix wall and wash pixels into coloured speckles.
    wash.rotation.y = title.rotationY; wash.translateZ(0.025); wash.renderOrder = -1; scene.add(wash);
  }
  const silverFrames = layout.placed.map((p) => {
    const a = artworks[p.index];
    const w = a.widthInches * 0.0254 * (a.scale ?? 1), h = a.heightInches * 0.0254 * (a.scale ?? 1);
    const group = new T.Group(); group.position.set(p.x, p.y, p.z); group.rotation.y = p.rotationY; scene.add(group);
    const back = new T.Mesh(new T.BoxGeometry(w, h, 0.03), backMat); back.position.z = 0.015; group.add(back);
    const material = new T.MeshStandardMaterial({ color: '#7a2a24', roughness: 0.62, metalness: 0.08, emissive: '#ffffff', emissiveIntensity: 0 });
    const front = new T.Mesh(new T.PlaneGeometry(w, h), material); front.position.z = 0.032; front.userData.artworkIndex = p.index; group.add(front); rayTargets.push(front);
    return { index: p.index, a, group, front, material, w, h, level: 0, want: 0, label: null, labelLang: null };
  });
  // ── Brass artworks: hung by wall and slot, with a soft warm wash on the wall behind each ──
  const brassGlow = glowMat.clone(); brassGlow.color.set(theme.spotlightColor || '#ffe4b2'); brassGlow.opacity = theme.brassGlowOpacity ?? 0.18;
  const brassFrames = artworks.map((a, index) => ({ a, index })).filter(({ a }) => roomOf(a) === 'brass').map(({ a, index }) => {
    const p = brassPlacement(a);
    const w = a.widthInches * 0.0254 * (a.scale ?? 1), h = a.heightInches * 0.0254 * (a.scale ?? 1);
    const group = new T.Group(); group.position.set(p.x, a.height ?? 1.5, p.z); group.rotation.y = p.rotationY; scene.add(group);
    const back = new T.Mesh(new T.BoxGeometry(w, h, 0.03), backMat); back.position.z = 0.015; back.castShadow = true; group.add(back);
    const material = new T.MeshStandardMaterial({ color: '#6b5432', roughness: 0.62, metalness: 0.08, emissive: '#ffffff', emissiveIntensity: 0, transparent: true });
    const front = new T.Mesh(new T.PlaneGeometry(w, h), material); front.position.z = 0.032; front.userData.artworkIndex = index; group.add(front); rayTargets.push(front);
    const wash = new T.Mesh(new T.PlaneGeometry(w + 0.75, h + 0.95), brassGlow);
    wash.position.set(0, -0.06, 0.023); wash.renderOrder = -1; group.add(wash);
    return { index, a, group, front, material, w, h, level: 0, want: 0, label: null, labelLang: null, brass: true };
  });
  const frames = [...silverFrames, ...brassFrames];
  const silverPos = new T.Vector3();

  // Texture streaming: thumbnails within ~9 m, full resolution for the closest pieces.
  const loader = new T.TextureLoader();
  let queue = []; let active = 0; const MAX_ACTIVE = 4;
  function pump() {
    // Drop jobs that are no longer wanted and start the closest pieces first.
    queue = queue.filter((job) => job.f.want >= job.level);
    queue.sort((p, q) => p.f.distance - q.f.distance);
    while (active < MAX_ACTIVE && queue.length) {
      const job = queue.shift(); active += 1;
      loader.loadAsync(job.url).then((tex) => {
        tex.colorSpace = T.SRGBColorSpace; tex.anisotropy = maxAnisotropy;
        job.done(tex);
      }).catch(() => job.done(null)).finally(() => { active -= 1; pump(); });
    }
  }
  function setTexture(f, tex, level) {
    if (!tex) return;
    if (f.want < level || disposed) { tex.dispose(); return; }
    // Older brass images carry a crop rectangle; thumbnails are always pre-cropped.
    const [cx, cy, cw, ch] = f.a.crop || [0, 0, 0, 0], iw = tex.image?.width, ih = tex.image?.height;
    if (level === 2 || !f.a.thumb) { if (iw && cw && (cw < iw || ch < ih)) { tex.offset.set(cx / iw, 1 - (cy + ch) / ih); tex.repeat.set(cw / iw, ch / ih); } }
    const old = f.material.map; f.material.map = tex; f.material.color.set('#ffffff');
    f.material.emissiveMap = tex; f.material.emissiveIntensity = 0.22; f.material.needsUpdate = true; f.level = level;
    if (old && old !== tex) old.dispose();
  }
  function request(f, level) {
    if (f.pending === level || f.level === level) return;
    const a = f.a;
    const path = level === 2 || !a.thumb ? a.image : a.thumb;
    f.pending = level;
    queue = queue.filter((job) => job.f !== f);
    queue.push({ f, level, url: imageUrl(path), done: (tex) => { if (f.pending === level) f.pending = 0; setTexture(f, tex, level); } });
    pump();
  }
  function drop(f) {
    if (f.material.map) { f.material.map.dispose(); f.material.map = null; f.material.emissiveMap = null; f.material.emissiveIntensity = 0; f.material.color.set(f.brass ? '#6b5432' : '#7a2a24'); f.material.needsUpdate = true; }
    f.level = 0;
  }
  function updateLabel(f, show) {
    if (show && (!f.label || f.labelLang !== language)) {
      if (!f.label) {
        // Brass labels keep the original, larger plaque; silver uses the compact one.
        f.label = f.brass
          ? new T.Mesh(new T.PlaneGeometry(0.31, 0.116), new T.MeshBasicMaterial({ map: labelTexture(f.index, language) }))
          : new T.Mesh(new T.PlaneGeometry(0.26, 0.0975), new T.MeshBasicMaterial({ map: labelTexture(f.index, language, 512) }));
        f.label.position.set(0, -f.h / 2 - (f.brass ? 0.105 : 0.09), f.brass ? 0.045 : 0.006); f.label.userData.artworkIndex = f.index; f.group.add(f.label); rayTargets.push(f.label);
      } else { f.label.material.map.dispose(); f.label.material.map = f.brass ? labelTexture(f.index, language) : labelTexture(f.index, language, 512); }
      f.labelLang = language;
    } else if (!show && f.label) {
      f.group.remove(f.label); f.label.material.map.dispose(); f.label.material.dispose(); f.label.geometry.dispose();
      rayTargets.splice(rayTargets.indexOf(f.label), 1); f.label = null; f.labelLang = null;
    }
  }
  let streamClock = 0;
  function stream(dt, force = false) {
    streamClock -= dt; if (streamClock > 0 && !force) return; streamClock = 0.3;
    const cam = camera.position;
    const ranked = frames.map((f) => ({ f, d: f.group.getWorldPosition(silverPos).distanceTo(cam) })).sort((p, q) => p.d - q.d);
    ranked.forEach(({ f, d }, rank) => {
      f.distance = d;
      f.want = d < 2.6 && rank < 6 ? 2 : d < 9.5 && rank < 48 ? 1 : 0;
      if (f.want === 0 && d > 12) drop(f);
      else if (f.want === 1 && f.level === 2 && d > 4) { f.level = 0; request(f, 1); }
      else if (f.want > f.level) request(f, f.want);
      else if (f.pending && f.pending > f.want) f.pending = 0;
      updateLabel(f, f.brass ? d < 6.5 : d < 4 && rank < 14);
    });
  }
  stream(0, true);

  // Compile and draw one frame now. If a shader fails, retry without shadows; if it still fails,
  // reject so the page shows the artwork list instead of an empty room.
  const firstFrame = () => { shaderFailed = false; renderer.render(scene, camera); return !shaderFailed; };
  if (!firstFrame() && renderer.shadowMap.enabled) {
    renderer.shadowMap.enabled = false;
    scene.traverse((o) => { if (o.isLight) o.castShadow = false; if (o.isMesh) { const mats = Array.isArray(o.material) ? o.material : [o.material]; mats.forEach((m) => { m.needsUpdate = true; }); } });
    if (!firstFrame()) { renderer.dispose(); renderer.domElement.remove(); throw new Error('WebGL shaders failed to compile'); }
  } else if (shaderFailed) { renderer.dispose(); renderer.domElement.remove(); throw new Error('WebGL shaders failed to compile'); }

  let frame = 0; let tick; let last = performance.now();
  const render = (now) => {
    const dt = Math.min((now - last) / 1000, 0.035); last = now;
    tick?.(dt); stream(dt); renderer.render(scene, camera); frame = requestAnimationFrame(render);
  };
  frame = requestAnimationFrame(render);
  const resize = () => { camera.aspect = host.clientWidth / host.clientHeight; camera.updateProjectionMatrix(); renderer.setSize(host.clientWidth, host.clientHeight, false); };
  const observer = new ResizeObserver(resize); observer.observe(host); onReady();

  return {
    camera, renderer, scene, rayTargets, canWalk,
    hungCount: silverFrames.length,
    streamStats() { return { levels: frames.reduce((n, f) => { n[f.level] = (n[f.level] || 0) + 1; return n; }, {}), labels: frames.filter((f) => f.label).length, queued: queue.length, active }; },
    zone() { return zoneAt(camera.position.x, camera.position.z); },
    walkSpeed() { return walkSpeedAt(camera.position.x, camera.position.z); },
    entrancePose(kind) { return ENTRANCES[kind] || ENTRANCES.brass; },
    setTick(fn) { tick = fn; },
    setLanguage(lang) {
      language = lang;
      stream(0, true);
    },
    dispose() {
      disposed = true; cancelAnimationFrame(frame); observer.disconnect(); queue.length = 0;
      scene.traverse((o) => { if (o instanceof T.Mesh) { o.geometry.dispose(); const mats = Array.isArray(o.material) ? o.material : [o.material]; mats.forEach((m) => { m.map?.dispose(); m.dispose(); }); } if (o.shadow) o.shadow.dispose(); });
      textures.forEach((t) => t.dispose()); renderer.dispose(); renderer.domElement.remove();
    },
  };
}
