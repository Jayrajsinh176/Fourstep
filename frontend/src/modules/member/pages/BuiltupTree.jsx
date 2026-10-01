import { useEffect, useRef, useState, useCallback, useMemo } from "react";
import Sidebar from "../components/Sidebar";
import Navbar from "../components/Navbar";
import { useNavigate } from "react-router-dom";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api";

// ─── Brand colors ─────────────────────────────────────────────────────────────
const BRAND = "#B0422E";
const BRAND_DARK = "#8c3422";
const BRAND_LIGHT = "#fdf0ed";

// ─── Layout constants ─────────────────────────────────────────────────────────
// NODE_SIZE   : icon box dimensions
// LABEL_H     : height reserved for uid + fullname below each icon
// V_GAP       : vertical space between bottom-of-label and top-of-next-icon
//               → connector lines live entirely inside this gap, NEVER on labels
// ROW_H       : total vertical distance between icon centres on adjacent rows
// H_GAP       : minimum empty horizontal space between neighbouring label edges
// LEAF_W      : horizontal slot claimed by each leaf node
const NODE_SIZE = 52;
const LABEL_H = 40;
const V_GAP = 60;
const ROW_H = NODE_SIZE + LABEL_H + V_GAP;
const H_GAP = 36;
const LEAF_W = 120 + H_GAP;   // ~156 px per leaf — labels are ~110 px wide max
const PAD_X = 56;
const PAD_TOP = 28;

// ─── Helpers ──────────────────────────────────────────────────────────────────
const isActive = (node) => {
  if (!node) return false;
  const s = String(node.status ?? "").toLowerCase().trim();
  return node.status === 1 || node.status === "1" || s === "active";
};

const formatDate = (v) => {
  if (!v) return "-";
  const d = new Date(v);
  if (Number.isNaN(d.getTime())) return String(v).slice(0, 10);
  return `${String(d.getDate()).padStart(2, "0")}-${String(d.getMonth() + 1).padStart(2, "0")}-${d.getFullYear()}`;
};

// Minimum canvas width for a subtree
const subtreeWidth = (node) => {
  if (!node) return LEAF_W;
  if (!node.left && !node.right) return LEAF_W;
  return subtreeWidth(node.left) + subtreeWidth(node.right);
};

const treeDepth = (n) => {
  if (!n) return 0;
  return 1 + Math.max(treeDepth(n.left), treeDepth(n.right));
};

// Build positions[] and edges[].
// cy = vertical centre of the icon box (not the whole node slot).
const buildLayout = (node, cx, cy, positions, edges) => {
  if (!node) return;
  positions.push({ node, cx, cy });
// Leaf node → don't show any empty children
if (!node.left && !node.right) {
  return;
}

// Left exists, Right missing
if (node.left && !node.right) {
  node.right = {
    isEmpty: true,
    parentId: node.user_id,
    position: "right",
  };
}

// Right exists, Left missing
if (!node.left && node.right) {
  node.left = {
    isEmpty: true,
    parentId: node.user_id,
    position: "left",
  };
}

  const lw = subtreeWidth(node.left);
  const rw = subtreeWidth(node.right);
  const lCx = cx - rw / 2;
  const rCx = cx + lw / 2;

  // Horizontal bar y-coordinate:
  //   starts at bottom of icon  →  cy + NODE_SIZE/2
  //   then clears the LABEL_H   →  + LABEL_H
  //   sits at midpoint of V_GAP →  + V_GAP/2
  // This ensures it is always between the label text above and the icon below.
  const barY = cy + NODE_SIZE / 2 + LABEL_H + V_GAP / 2;
  const childY = cy + ROW_H;
  const childTop = childY - NODE_SIZE / 2;   // top edge of child icon

  edges.push({ type: "v", x: cx, y1: cy + NODE_SIZE / 2, y2: barY });
  edges.push({ type: "h", x1: lCx, x2: rCx, y: barY });
  edges.push({ type: "v", x: lCx, y1: barY, y2: childTop });
  edges.push({ type: "v", x: rCx, y1: barY, y2: childTop });

  buildLayout(node.left, lCx, childY, positions, edges);
  buildLayout(node.right, rCx, childY, positions, edges);
};

// ─── Person SVG ───────────────────────────────────────────────────────────────
const PersonIcon = ({ color, size = 20 }) => (
  <svg viewBox="0 0 24 24" fill={color} style={{ width: size, height: size, flexShrink: 0 }}>
    <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
  </svg>
);

// ─── Tooltip ──────────────────────────────────────────────────────────────────
const MemberTooltip = ({ member, position, visible, onEnter, onLeave }) => {
  if (!member) return null;

  const rows = [
    ["Reg. Date", formatDate(member.created_at)],
    ["Activation", `₹ ${member.activation_amount ?? "0.00"}`],
    ["Self PV", member.self_pv ?? 0],
    ["Team A | Team B", `${member.team_a ?? 0} | ${member.team_b ?? 0}`],
    ["Left PV | Right PV", `${member.left_pv ?? 0} | ${member.right_pv ?? 0}`],
    ["Total PV", member.total_pv ?? 0],
    ["Designation", member.designation ?? "Associate"],
    ["KYC Status", member.kyc_status ?? "UnVerified"],
    ["City", member.city ?? "-"],
  ];

  return (
    <div
      onMouseEnter={onEnter}
      onMouseLeave={onLeave}
      style={{
        position: "fixed",
        top: position.top,
        left: position.left,
        zIndex: 9999,
        width: 260,
        borderRadius: 16,
        overflow: "hidden",
        opacity: visible ? 1 : 0,
        pointerEvents: visible ? "auto" : "none",
        transition: "opacity 0.14s",
        boxShadow: `0 16px 48px rgba(176,66,46,0.20), 0 2px 10px rgba(0,0,0,0.10)`,
        border: `1px solid ${BRAND}30`,
      }}
    >
      {/* Header band */}
      <div style={{
        background: BRAND,
        padding: "11px 14px 9px",
        display: "flex",
        alignItems: "center",
        gap: 10,
      }}>
        <div style={{
          width: 34, height: 34, borderRadius: 9,
          background: "rgba(255,255,255,0.2)",
          display: "flex", alignItems: "center", justifyContent: "center",
        }}>
          <PersonIcon color="#fff" size={17} />
        </div>
        <div style={{ minWidth: 0 }}>
          <div style={{ color: "#fff", fontWeight: 700, fontSize: 13, lineHeight: 1.3, whiteSpace: "nowrap", overflow: "hidden", textOverflow: "ellipsis" }}>
            {member.user_id}
          </div>
          <div style={{ color: "rgba(255,255,255,0.72)", fontSize: 11, marginTop: 1 }}>
            {member.fullname}
          </div>
        </div>
      </div>

      {/* Data rows */}
      <div style={{ background: "#fff" }}>
        {rows.map(([label, val], i) => (
          <div key={label} style={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
            padding: "5px 14px",
            background: i % 2 === 0 ? BRAND_LIGHT : "#fff",
          }}>
            <span style={{ fontSize: 11, color: BRAND_DARK, fontWeight: 600, flexShrink: 0, marginRight: 8 }}>
              {label}
            </span>
            <span style={{ fontSize: 11, color: "#374151", fontWeight: 500, textAlign: "right" }}>
              {val}
            </span>
          </div>
        ))}
      </div>
    </div>
  );
};

// ─── Single tree node ─────────────────────────────────────────────────────────
function NodeElement({ node, cx, cy, active, onEnter, onLeave, onMemberClick, openSignup, }) {
  const iconRef = useRef(null);
  const [hov, setHov] = useState(false);

  if (node?.isEmpty) {
    return (
      <div
        style={{
          position: "absolute",
          left: cx,
          top: cy - NODE_SIZE / 2,
          transform: "translateX(-50%)",
          display: "flex",
          flexDirection: "column",
          alignItems: "center",
          width: LEAF_W - 8,
        }}
      >
        <div
          onClick={() => openSignup(node.parentId, node.position)}
          style={{
            width: NODE_SIZE,
            height: NODE_SIZE,
            borderRadius: 13,
            border: "2px dashed #cbd5e1",
            background: "#f8fafc",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            cursor: "pointer",
          }}
        >
          <PersonIcon color="#9ca3af" />
        </div>

      </div>
    );
  }

  const bg = active ? "#dcfce7" : "#fee2e2";
  const color = active ? "#16a34a" : "#dc2626";

  return (
    <div style={{
      position: "absolute",
      left: cx,
      top: cy - NODE_SIZE / 2,
      transform: "translateX(-50%)",
      display: "flex",
      flexDirection: "column",
      alignItems: "center",
      width: LEAF_W - 8,         // slightly narrower than slot so edges don't touch
    }}>
      {/* ── Icon ── */}
      <div
        ref={iconRef}
        onMouseEnter={() => { setHov(true); onEnter(node, iconRef.current); }}
        onMouseLeave={() => { setHov(false); onLeave(); }}
        style={{
          width: NODE_SIZE,
          height: NODE_SIZE,
          borderRadius: 13,
          background: bg,
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          cursor: active ? "pointer" : "default",
          flexShrink: 0,
          boxShadow: hov && active
            ? `0 0 0 3px #86efac, 0 6px 18px rgba(176,66,46,0.15)`
            : "0 1px 5px rgba(0,0,0,0.08)",
          transform: hov && active ? "scale(1.09)" : "scale(1)",
          transition: "box-shadow 0.16s, transform 0.16s",
        }}
      >
        <PersonIcon color={color} />
      </div>

      {/* ── Labels ── fixed-height block so lines always clear them ── */}
      <div style={{
        marginTop: 6,
        height: LABEL_H,
        width: "100%",
        textAlign: "center",
        overflow: "hidden",
      }}>
        <div
          onClick={() => onMemberClick(node.user_id)}
          style={{
            fontSize: 11,
            fontWeight: 700,
            color: "#111827",
            cursor: "pointer",
            textDecoration: "underline",
            lineHeight: 1.35,
            whiteSpace: "nowrap",
            overflow: "hidden",
            textOverflow: "ellipsis",
            padding: "0 6px",
          }}
        >
          {node.user_id}
        </div>
        <div style={{
          fontSize: 10,
          color: "#9ca3af",
          marginTop: 3,
          lineHeight: 1.3,
          whiteSpace: "nowrap",
          overflow: "hidden",
          textOverflow: "ellipsis",
          padding: "0 6px",
        }}>
          {node.fullname}
        </div>
      </div>
    </div>
  );
}

// ─── Page ─────────────────────────────────────────────────────────────────────
export default function BuiltupTree() {
  const [tree, setTree] = useState(null);
  const navigate = useNavigate();
  const [treeHistory, setTreeHistory] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchId, setSearchId] = useState("");
  const [selectedMember, setSelectedMember] = useState(null);
  const [tooltipPos, setTooltipPos] = useState({ top: 0, left: 0 });
  const [tooltipVisible, setTooltipVisible] = useState(false);
  const hideTimer = useRef(null);

  // Fetch
  useEffect(() => {
    const run = async () => {
      try {
        const md = JSON.parse(localStorage.getItem("memberData") || "{}");
        const res = await fetch(`${API_BASE_URL}/member/tree`, {
          headers: { Accept: "application/json", "X-Auth-Member": md.user_id || "" },
        });
        const data = await res.json();
        setTree(data.tree);
      } catch (e) {
        console.error(e);
      } finally {
        setLoading(false);
      }
    };
    run();
    return () => clearTimeout(hideTimer.current);
  }, []);

  const clearHide = useCallback(() => clearTimeout(hideTimer.current), []);

  const handleNodeEnter = useCallback((member, el) => {
    if (!isActive(member) || !el) return;
    clearHide();
    const r = el.getBoundingClientRect();
    const tipW = 260, tipH = 290;
    const mob = window.innerWidth < 640;

    let left = r.right + 14;
    let top = r.top - 10;

    if (mob) {
      left = Math.max(8, Math.min(r.left - 20, window.innerWidth - tipW - 8));
      top = r.bottom + 10;
    } else if (left + tipW > window.innerWidth - 12) {
      left = r.left - tipW - 14;
    }

    top = Math.min(Math.max(top, 8), window.innerHeight - tipH - 8);
    setSelectedMember(member);
    setTooltipPos({ top, left });
    setTooltipVisible(true);
  }, [clearHide]);

  const handleNodeLeave = useCallback(() => {
    clearHide();
    hideTimer.current = setTimeout(() => setTooltipVisible(false), 130);
  }, [clearHide]);

  // Layout
  const { positions, edges, canvasW, canvasH } = useMemo(() => {
    if (!tree) return { positions: [], edges: [], canvasW: 600, canvasH: 300 };
    const totalW = subtreeWidth(tree);
    const depth = treeDepth(tree);
    const cW = totalW + PAD_X * 2;
    const cH = PAD_TOP + depth * ROW_H + LABEL_H + 36;
    const rootCx = PAD_X + totalW / 2;
    const pos = [], edg = [];
    buildLayout(tree, rootCx, PAD_TOP + NODE_SIZE / 2, pos, edg);
    return { positions: pos, edges: edg, canvasW: cW, canvasH: cH };
  }, [tree]);

  const svgW = Math.max(canvasW, 400);

  useEffect(() => {
    loadDefaultTree();

    return () => clearTimeout(hideTimer.current);
  }, []);

  const loadDefaultTree = async () => {
    try {
      const md = JSON.parse(localStorage.getItem("memberData") || "{}");

      const res = await fetch(`${API_BASE_URL}/member/tree`, {
        headers: {
          Accept: "application/json",
          "X-Auth-Member": md.user_id || "",
        },
      });

      const data = await res.json();
      setTree(data.tree);
    } catch (e) {
      console.error(e);
    }
  };

  const loadTree = async (userId) => {
    try {
      const md = JSON.parse(localStorage.getItem("memberData") || "{}");

      const res = await fetch(
        `${API_BASE_URL}/member/tree?user_id=${userId}`,
        {
          headers: {
            Accept: "application/json",
            "X-Auth-Member": md.user_id || "",
          },
        }
      );

  const data = await res.json();

if (res.status === 403) {
  alert(data.message);
  setSearchId("");
  return;
}

if (data.tree) {

  if (tree) {
    setTreeHistory(prev => [...prev, tree]);
  }

  setTree(data.tree);
  setSearchId(userId);

} else {
  alert("User not found");
}
    } catch (err) {
      console.error(err);
      alert("Search failed");
    }
  };

  const handleBack = () => {

    if (treeHistory.length === 0) return;

    const previousTree = treeHistory[treeHistory.length - 1];

    setTree(previousTree);

    setSearchId(previousTree.user_id);

    setTreeHistory(prev => prev.slice(0, -1));
  };

  const handleSearch = () => {
    if (!searchId.trim()) return;

    loadTree(searchId);
  };

  const openSignup = (sponsorId, position) => {

    navigate(
      `/member/signup?sponsorId=${sponsorId}&position=${position}`
    );

  };

  return (
    <div style={{ display: "flex", background: "#f1f5f9", minHeight: "100vh" }}>
      <Sidebar />

      <div style={{ flex: 1, display: "flex", flexDirection: "column", minWidth: 0 }}>
        <Navbar pageTitle="Builtup Tree" />

        <div style={{ padding: "20px 24px 40px" }}>

          <h1 style={{
            textAlign: "center", fontSize: 26, fontWeight: 700,
            color: BRAND, marginBottom: 20, letterSpacing: "-0.3px",
          }}>
            Builtup Tree
          </h1>

          {/* Main card */}
          <div style={{
            background: "#fff", borderRadius: 20,
            boxShadow: "0 2px 18px rgba(0,0,0,0.07)",
            padding: "24px 28px 32px",
          }}>

            {/* Search row */}
            <div style={{ display: "flex", flexWrap: "wrap", alignItems: "center", gap: 10, marginBottom: 22 }}>
              <span style={{ color: "#6b7280", fontSize: 14, fontWeight: 500 }}>
                Search Associate :
              </span>
              <input
                value={searchId}
                onChange={(e) => {
                  const value = e.target.value;
                  setSearchId(value);

                  if (!value.trim()) {
                    loadDefaultTree();
                  }
                }}
                placeholder="Enter user ID"
                style={{
                  background: "#f9fafb", border: "1.5px solid #e5e7eb",
                  borderRadius: 8, padding: "8px 14px", outline: "none",
                  fontSize: 14, width: 220, transition: "border-color 0.15s",
                }}
                onFocus={e => (e.target.style.borderColor = BRAND)}
                onBlur={e => (e.target.style.borderColor = "#e5e7eb")}
              />
              <button
                style={{
                  background: BRAND, color: "#fff", border: "none",
                  borderRadius: 8, padding: "8px 22px", fontSize: 14,
                  cursor: "pointer", fontWeight: 600, transition: "background 0.15s",
                }}
                onMouseOver={e => (e.currentTarget.style.background = BRAND_DARK)}
                onMouseOut={e => (e.currentTarget.style.background = BRAND)}
                onClick={handleSearch}
              >
                Search
              </button>

              <button
                onClick={handleBack}
                disabled={treeHistory.length === 0}
                style={{
                  background: treeHistory.length ? "#6b7280" : "#d1d5db",
                  color: "#fff",
                  border: "none",
                  borderRadius: 8,
                  padding: "8px 22px",
                  cursor: treeHistory.length ? "pointer" : "not-allowed",
                }}
              >
                ← Back
              </button>
            </div>

            {/* Legend */}
            <div style={{ display: "flex", gap: 20, flexWrap: "wrap", justifyContent: "center", marginBottom: 22 }}>
              {[
                { label: "Empty", bg: "#f3f4f6", clr: "#9ca3af" },
                { label: "In Process", bg: "#fee2e2", clr: "#dc2626" },
                { label: "Active", bg: "#dcfce7", clr: "#16a34a" },
              ].map(({ label, bg, clr }) => (
                <div key={label} style={{ display: "flex", alignItems: "center", gap: 8, fontSize: 13, color: "#6b7280" }}>
                  <div style={{
                    width: 34, height: 34, borderRadius: 9, background: bg,
                    display: "flex", alignItems: "center", justifyContent: "center",
                  }}>
                    <PersonIcon color={clr} />
                  </div>
                  {label}
                </div>
              ))}
            </div>

            {/* Left / Right stats */}
            <div style={{ display: "flex", gap: 12, marginBottom: 24 }}>
              <div style={{
                flex: 1, background: "#eff6ff", borderRadius: 12,
                padding: "10px 16px", color: "#1d4ed8", fontSize: 13,
                fontWeight: 700, lineHeight: 1.9, textAlign: "center",
              }}>
                <div>Left : {tree?.left_count ?? 0}</div>
                <div>Left PV : {tree?.left_pv ?? 0}</div>
                <div>Left BV : {tree?.left_bv ?? 0}</div>
              </div>
              <div style={{
                flex: 1, background: "#eff6ff", borderRadius: 12,
                padding: "10px 16px", color: "#1d4ed8", fontSize: 13,
                fontWeight: 700, lineHeight: 1.9, textAlign: "center",
              }}>
                <div>Right : {tree?.right_count ?? 0}</div>
                <div>Right PV : {tree?.right_pv ?? 0}</div>
                <div>Right BV : {tree?.right_bv ?? 0}</div>
              </div>
            </div>

            {/* ── Tree canvas ── */}
            {loading ? (
              <div style={{ textAlign: "center", color: "#9ca3af", padding: "48px 0", fontSize: 15 }}>
                Loading tree…
              </div>
            ) : !tree ? (
              <div style={{ textAlign: "center", color: "#9ca3af", padding: "48px 0", fontSize: 15 }}>
                No Tree Data Found
              </div>
            ) : (
              <div style={{ overflowX: "auto", paddingBottom: 4 }}>
                <div style={{
                  position: "relative",
                  width: svgW,
                  height: canvasH,
                  margin: "0 auto",
                }}>
                  {/* SVG lines — drawn on a single layer, always behind nodes */}
                  <svg
                    style={{ position: "absolute", top: 0, left: 0, pointerEvents: "none", overflow: "visible" }}
                    width={svgW}
                    height={canvasH}
                    viewBox={`0 0 ${svgW} ${canvasH}`}
                  >
                    {edges.map((e, i) =>
                      e.type === "h" ? (
                        <line key={i}
                          x1={e.x1} y1={e.y} x2={e.x2} y2={e.y}
                          stroke="#d1d5db" strokeWidth="1.5" strokeLinecap="round"
                        />
                      ) : (
                        <line key={i}
                          x1={e.x} y1={e.y1} x2={e.x} y2={e.y2}
                          stroke="#d1d5db" strokeWidth="1.5" strokeLinecap="round"
                        />
                      )
                    )}
                  </svg>

                  {/* Node divs — rendered on top of SVG lines */}
                  {positions.map(({ node, cx, cy }, i) => (
                    <NodeElement
                      key={i}
                      node={node}
                      cx={cx}
                      cy={cy}
                      active={isActive(node)}
                      onEnter={handleNodeEnter}
                      onLeave={handleNodeLeave}
                      onMemberClick={loadTree}
                      openSignup={openSignup}
                    />
                  ))}
                </div>
              </div>
            )}
          </div>{/* /card */}
        </div>
      </div>

      {/* Tooltip */}
      <MemberTooltip
        member={selectedMember}
        position={tooltipPos}
        visible={tooltipVisible}
        onEnter={clearHide}
        onLeave={() => setTooltipVisible(false)}
      />
    </div>
  );
}