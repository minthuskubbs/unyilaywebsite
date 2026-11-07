// Floor plan (metres; y is up, the camera stands at 1.58 m).
//
//                 z = 34.25 ┌──────── feature wall ────────┐
//                           │                              │
//                           │   SILVER HALL  (12 × 32 m)   │
//                           │   one open hall, artworks    │
//                           │   grouped by design with a   │
//                           │   title above each group     │
//                 z = 2.25  └───┬────[pass-through]────┬───┘
//                               │                        │
//                               │  BRASS ROOM (5.8 × 6 m) │
//                 z = -3.75     └────────────────────────┘
//                             x=-6  x=-2.9          x=2.9  x=6
//
// Brass artworks are placed by wall and slot: on the back and front walls the slot is x,
// on the left and right walls it is z (metres, -3 to 3).
// The pass-through in the shared wall is the Brass Entrance (seen from the hall) and the
// Silver Entrance (seen from the brass room).

export const WALL = 0.14;
export const BRASS = { x0: -2.9, x1: 2.9, z0: -3.75, z1: 2.25, height: 2.8 };
export const SILVER = { x0: -6, x1: 6, z0: 2.25, z1: 34.25, height: 3.4 };
export const PASSAGE = { x0: -0.65, x1: 0.65, height: 2.4 };

// Where each link lands. ?entrance=silver (default) or ?entrance=brass.
export const ENTRANCES = {
  brass: { position: [1.25, 1.58, 1.55], lookAt: [-0.9, 1.4, -3.2] },
  silver: { position: [0, 1.58, 3.4], lookAt: [0, 1.45, 16] },
};

const R = 0.3; // body radius kept clear of walls
const inside = (r, x, z) => x > r.x0 && x < r.x1 && z > r.z0 && z < r.z1;
const WALKABLE = [
  { x0: BRASS.x0 + WALL / 2 + R, x1: BRASS.x1 - WALL / 2 - R, z0: BRASS.z0 + WALL / 2 + R, z1: BRASS.z1 - WALL / 2 - 0.25 }, // brass room
  { x0: PASSAGE.x0 + 0.22, x1: PASSAGE.x1 - 0.22, z0: 1.9, z1: 2.7 },
  { x0: SILVER.x0 + WALL / 2 + R, x1: SILVER.x1 - WALL / 2 - R, z0: SILVER.z0 + WALL / 2 + R, z1: SILVER.z1 - WALL / 2 - R },
];
export const canWalk = (x, z) => WALKABLE.some((r) => inside(r, x, z));
export const zoneAt = (x, z) => (z > BRASS.z1 ? 'silver' : 'brass');
export const walkSpeedAt = (x, z) => (z > BRASS.z1 + 0.6 ? 1.6 : 1.15);

// Where a brass artwork hangs: {x, z, rotationY} on the wall face.
export function brassPlacement(a) {
  const face = WALL / 2 + 0.002;
  const slot = Number(a.slot) || 0;
  if (a.wall === 'left') return { x: BRASS.x0 + face, z: slot, rotationY: Math.PI / 2 };
  if (a.wall === 'right') return { x: BRASS.x1 - face, z: slot, rotationY: -Math.PI / 2 };
  if (a.wall === 'front') return { x: slot, z: BRASS.z1 - face, rotationY: Math.PI };
  return { x: slot, z: BRASS.z0 + face, rotationY: 0 };
}

const INCH = 0.0254;
const CENTRE = 1.5; // gallery centre line for every group
const COLUMN_GAP = 0.3; // between pieces inside a group
const STACK_GAP = 0.2; // vertical gap in a stacked pair (holds the top piece's label)
const GROUP_GAP = 0.85; // minimum space between groups
const SMALL = 10.5; // pieces this tall or less (inches) are hung in stacked pairs

// Groups: one per design (English name); designs with a single piece share a "Heritage" group.
export function groupSilver(items) {
  const byName = new Map();
  for (const item of items) {
    const key = String(item.en || item.id).trim().toLowerCase();
    if (!byName.has(key)) byName.set(key, { key, my: item.my, en: item.en, items: [] });
    byName.get(key).items.push(item);
  }
  const groups = [];
  const heritage = { key: 'heritage', my: 'ရိုးရာလက်ရာများ', en: 'Heritage Works', items: [] };
  for (const g of byName.values()) (g.items.length > 1 ? groups : heritage.items).push(g.items.length > 1 ? g : g.items[0]);
  if (heritage.items.length) groups.push(heritage);
  return groups.map((g) => ({ ...g, columns: composeColumns(g.items) })).map((g) => ({ ...g, width: groupWidth(g.columns) }));
}

const size = (a) => ({ w: a.widthInches * INCH * (a.scale ?? 1), h: a.heightInches * INCH * (a.scale ?? 1) });

// Columns are a single piece or a stacked pair of small pieces; arranged symmetrically,
// the largest column in the middle and the rest alternating right and left.
function composeColumns(items) {
  const sorted = [...items].sort((a, b) => size(b).w * size(b).h - size(a).w * size(a).h);
  const big = sorted.filter((a) => a.heightInches > SMALL);
  const small = sorted.filter((a) => a.heightInches <= SMALL).sort((a, b) => size(b).w - size(a).w);
  const columns = big.map((a) => [a]);
  while (small.length >= 2) columns.push([small.shift(), small.shift()]);
  if (small.length) columns.push([small.shift()]);
  columns.sort((p, q) => colHeight(q) - colHeight(p) || colWidth(q) - colWidth(p));
  const arranged = [];
  columns.forEach((c, i) => (i % 2 ? arranged.unshift(c) : arranged.push(c)));
  return arranged;
}
const colWidth = (c) => Math.max(...c.map((a) => size(a).w));
const colHeight = (c) => c.reduce((h, a) => h + size(a).h, 0) + (c.length - 1) * STACK_GAP;
const groupWidth = (columns) => columns.reduce((w, c) => w + colWidth(c), 0) + COLUMN_GAP * (columns.length - 1);

// Hanging runs in visiting order: left wall (entrance → far end), the feature wall,
// the right wall (far end → entrance), then the entrance wall either side of the pass-through.
function hallRuns() {
  const face = WALL / 2 + 0.002;
  const corner = 0.6;
  const run = (x, z, dx, dz, nx, nz, length, name) => ({ x, z, dx, dz, nx, nz, length, name });
  const depth = SILVER.z1 - SILVER.z0 - 2 * corner;
  return [
    run(SILVER.x1 - face, SILVER.z0 + corner, 0, 1, -1, 0, depth, 'left'),
    run(SILVER.x1 - corner, SILVER.z1 - face, -1, 0, 0, -1, SILVER.x1 - SILVER.x0 - 2 * corner, 'feature'),
    run(SILVER.x0 + face, SILVER.z1 - corner, 0, -1, 1, 0, depth, 'right'),
    run(SILVER.x0 + corner, SILVER.z0 + face, 1, 0, 0, 1, PASSAGE.x0 - 0.5 - (SILVER.x0 + corner), 'entrance-right'),
    run(PASSAGE.x1 + 0.5, SILVER.z0 + face, 1, 0, 0, 1, SILVER.x1 - corner - (PASSAGE.x1 + 0.5), 'entrance-left'),
  ];
}

// Returns {placed: [{index, x, y, z, rotationY, labelBelow}], titles: [{x, y, z, rotationY, my, en, width}], overflow}.
export function layoutSilver(items) {
  const groups = groupSilver(items);
  const runs = hallRuns();
  // The group with the widest single piece becomes the centrepiece of the feature wall.
  const featureIndex = groups.reduce((best, g, i) => (Math.max(...g.items.map((a) => size(a).w)) > Math.max(...groups[best].items.map((a) => size(a).w)) ? i : best), 0);
  // Assign the widest groups first (tighter packing), then restore the design order on each wall.
  const queue = groups.map((g, order) => ({ ...g, order })).filter((g) => g.order !== featureIndex).sort((p, q) => q.width - p.width);
  const byRun = new Map(runs.map((r) => [r, []]));
  byRun.get(runs[1]).push(groups[featureIndex]);
  const used = (r) => byRun.get(r).reduce((w, g) => w + g.width, 0) + GROUP_GAP * (byRun.get(r).length + 1);
  // Fill the long walls in visiting order, alternating sides so both walls stay balanced, then the rest.
  const order = [runs[0], runs[2], runs[1], runs[3], runs[4]];
  // The feature wall is kept for the centrepiece and the entrance wall for last; both are used only when the long walls are full.
  const weight = (r) => (r.name === 'left' || r.name === 'right' ? 1 : 0.05);
  const overflow = [];
  for (const g of queue) {
    const candidates = order.filter((r) => used(r) + g.width + GROUP_GAP <= r.length);
    if (!candidates.length) { overflow.push(...g.items.map((a) => a.index)); continue; }
    // Prefer the long wall with the most free space; short walls only when the long ones are full.
    const target = candidates.sort((p, q) => (q.length - used(q)) * weight(q) - (p.length - used(p)) * weight(p))[0];
    byRun.get(target).push(g);
  }
  const placed = [];
  const titles = [];
  // Hang `list` between `from` and `to` (metres along the run) with equal space around every group.
  function hang(r, list, from, to) {
    if (!list.length) return;
    const gap = (to - from - list.reduce((w, g) => w + g.width, 0)) / (list.length + 1);
    const rotationY = Math.atan2(r.nx, r.nz);
    let s = from + gap;
    for (const g of list) {
      const centre = s + g.width / 2;
      let c = s;
      let top = 0;
      for (const col of g.columns) {
        const w = colWidth(col);
        const along = c + w / 2;
        const x = r.x + r.dx * along;
        const z = r.z + r.dz * along;
        const h = colHeight(col);
        const centreY = Math.max(CENTRE, h / 2 + 0.7);
        let y = centreY + h / 2;
        for (const a of col) {
          const ah = size(a).h;
          placed.push({ index: a.index, x, y: y - ah / 2, z, rotationY });
          y -= ah + STACK_GAP;
        }
        top = Math.max(top, centreY + h / 2);
        c += w + COLUMN_GAP;
      }
      titles.push({ x: r.x + r.dx * centre, z: r.z + r.dz * centre, y: Math.max(top + 0.42, 2.45), rotationY, my: g.my, en: g.en, span: g.width });
      s += g.width + gap;
    }
  }
  for (const r of runs) {
    const list = byRun.get(r).sort((p, q) => (p.order ?? -1) - (q.order ?? -1));
    if (r.name === 'feature' && list.length) {
      // The centrepiece sits exactly in the middle of the feature wall; the rest flank it evenly.
      const [centre, ...rest] = list;
      const mid = r.length / 2;
      hang(r, [centre], mid - centre.width / 2 - 0.001, mid + centre.width / 2 + 0.001);
      const half = Math.ceil(rest.length / 2);
      hang(r, rest.slice(0, half), 0, mid - centre.width / 2 - GROUP_GAP / 2);
      hang(r, rest.slice(half), mid + centre.width / 2 + GROUP_GAP / 2, r.length);
    } else hang(r, list, 0, r.length);
  }
  return { placed, titles, overflow };
}
