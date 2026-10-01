<x-filament::page>

<style>
.tree-wrapper {
    width: 100%;
    overflow-x: auto;
    overflow-y: auto;
}

.tree-scroll {
    min-width: 100%;
    width: max-content;
    margin: 0 auto;
    display: flex;
    justify-content: center;
}

.tree {
    padding: 20px;
}

.tree ul {
    padding-top: 20px;
    position: relative;
    display: flex;
    justify-content: center;
    margin: 0;
    padding-left: 0;
}

.tree li {
    list-style-type: none;
    text-align: center;
    position: relative;
    padding: 20px 12px 0 12px;
}

.tree li::before,
.tree li::after {
    content: '';
    position: absolute;
    top: 0;
    right: 50%;
    border-top: 2px solid #cbd5e1;
    width: 50%;
    height: 20px;
}

.tree li::after {
    right: auto;
    left: 50%;
    border-left: 2px solid #cbd5e1;
}

.tree li:only-child::after,
.tree li:only-child::before {
    display: none;
}

.tree li:only-child {
    padding-top: 0;
}

.tree li:first-child::before,
.tree li:last-child::after {
    border: 0 none;
}

.tree li:last-child::before {
    border-right: 2px solid #cbd5e1;
    border-radius: 0 5px 0 0;
}

.tree li:first-child::after {
    border-radius: 5px 0 0 0;
}

.tree ul ul::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    border-left: 2px solid #cbd5e1;
    width: 0;
    height: 20px;
}

.node {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    cursor: default;
}

.box {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.active   { background: #dcfce7; }
.inactive { background: #fee2e2; }

.label {
    font-size: 10px;
    margin-top: 5px;
    width: 90px;
    text-align: center;
    word-break: break-word;
    line-height: 1.3;
}

/* user_id line = drill-down trigger (same behaviour as Member Panel Tree) */
.label .tree-uid {
    cursor: pointer;
    text-decoration: underline;
}

.tree-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin: 0 0 10px 0;
    position: sticky;
    left: 0;
    z-index: 10;
}

.tree-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 8px 14px;
    font-size: 13px;
    color: #475569;
    cursor: pointer;
    transition: background 0.15s;
}

.tree-back-btn:hover:not(:disabled) { background: #f1f5f9; }
.tree-back-btn:disabled { opacity: 0.45; cursor: not-allowed; }

.zoom-bar {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 6px 10px;
    width: fit-content;
    margin: 0;
}

.zoom-bar button {
    width: 32px;
    height: 32px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: transparent;
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s;
}

.zoom-bar button:hover { background: #f1f5f9; }

.zoom-label {
    font-size: 13px;
    color: #64748b;
    min-width: 40px;
    text-align: center;
}
</style>

<div class="tree-controls">
    <button id="treeBackBtn" class="tree-back-btn" onclick="handleBack()" disabled>&larr; Back</button>

    <div class="zoom-bar">
        <button onclick="changeZoom(-0.1)">&minus;</button>
        <span class="zoom-label" id="zoomLabel">100%</span>
        <button onclick="changeZoom(0.1)">+</button>
        <button onclick="resetZoom()" style="width:auto;padding:0 10px;font-size:12px;">Reset</button>
    </div>
</div>

<div class="tree-wrapper" id="treeWrapper">
    <div class="tree-scroll" id="treeScroll">
        <div id="tree" class="tree"></div>
    </div>
</div>

<script>
let zoom = 1;
const ZOOM_MIN = 0.3, ZOOM_MAX = 2.0;

/* Drill-down state (mirrors BuiltupTree.jsx: `tree` + `treeHistory`) */
let currentTree = null;
let treeHistory = [];

// Inject popup styles into head
const style = document.createElement('style');
style.textContent = `
    #memberPopup {
        position: fixed;
        background: #ffffff;
        color: #1e293b;
        border: 0.5px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.13);
        width: 240px;
        max-width: 240px;
        z-index: 2147483647;
        pointer-events: auto;
        overflow-y: auto;
        max-height: 70vh;
        font-size: 11px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }
`;
document.head.appendChild(style);

// Append popup directly to <html> element to escape all Filament containers
const popup = document.createElement('div');
popup.id = 'memberPopup';
popup.style.display = 'none';
document.documentElement.appendChild(popup);

function centerTree() {
    const wrapper = document.getElementById('treeWrapper');
    const scroll  = document.getElementById('treeScroll');
    wrapper.scrollLeft = (scroll.scrollWidth - wrapper.clientWidth) / 2;
}

function applyZoom() {
    document.getElementById('treeScroll').style.zoom = zoom;
    document.getElementById('zoomLabel').textContent = Math.round(zoom * 100) + '%';
    requestAnimationFrame(centerTree);
}

function changeZoom(delta) {
    zoom = Math.min(ZOOM_MAX, Math.max(ZOOM_MIN, parseFloat((zoom + delta).toFixed(1))));
    applyZoom();
}

function resetZoom() {
    zoom = 1;
    applyZoom();
}

function isActive(n) { return n && (n.status == 1 || n.status === "1"); }
function safe(v) { return (v === 0 || v === "0") ? 0 : (v || 0); }

function isActiveMember(node) {
    if (!node) return false;
    const status = String(node.status ?? "").toLowerCase();
    return node.status == 1 || status === "1" || status === "active";
}

function row(icon, label, value) {
    return `<div style="display:flex;align-items:center;justify-content:space-between;padding:3px 0;border-bottom:0.5px solid #f1f5f9;font-size:12px;">
        <span style="color:#64748b;">${icon} ${label}</span>
        <span style="font-weight:500;color:#0f172a;">${value}</span>
    </div>`;
}

function mini(label, value) {
    return `<div><div style="font-size:9px;color:#94a3b8;">${label}</div><div style="font-size:12px;font-weight:500;color:#0f172a;">${value}</div></div>`;
}

let hideTimer = null;
let isPopupHovered = false;

function showPopup(node, targetEl) {
    if (!isActiveMember(node)) return;
    clearTimeout(hideTimer);

    popup.style.visibility = 'hidden';
    popup.style.display = 'block';
    popup.style.top = '0px';
    popup.style.left = '0px';

    popup.innerHTML = `
        <div style="padding:14px 16px 12px;display:flex;align-items:center;gap:10px;border-bottom:0.5px solid #e2e8f0;">
            <div style="width:42px;height:42px;border-radius:50%;background:#dcfce7;border:2px solid #86efac;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:18px;">👤</div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:14px;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${node.user_id}</div>
                <div style="font-size:12px;color:#64748b;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${node.fullname || '—'}</div>
            </div>
            <span style="flex-shrink:0;font-size:11px;font-weight:600;padding:3px 10px;border-radius:999px;background:#dcfce7;color:#166534;border:0.5px solid #86efac;">Active</span>
        </div>
<div style="background:#f8fafc;border-radius:8px;padding:5px;margin-bottom:6px;display:flex;gap:5px;">
    <button onclick="viewMember('${node.user_id}')" style="flex:1;padding:4px 0;font-size:11px;font-weight:600;border-radius:6px;background:#dcfce7;color:#166534;border:none;cursor:pointer;">View</button>
    <button onclick="editMember('${node.id}')" style="flex:1;padding:4px 0;font-size:11px;font-weight:600;border-radius:6px;background:#dbeafe;color:#1e40af;border:none;cursor:pointer;">Edit</button>
</div>
        <div style="padding:8px 16px 10px;">
            ${row('⬆', 'Step', `<span style="background:#16a34a;color:#fff;font-size:10px;padding:1px 8px;border-radius:999px;">${node.step ?? 0}</span>`)}
            ${row('₹', 'Activation', '₹ ' + safe(node.activation_amount))}
            ${row('📅', 'Reg. Date', node.created_at ? new Date(node.created_at).toLocaleDateString('en-GB') : '-')}

${row('📊', 'Self BV', safe(node.self_bv))}

${row('👥', 'Team A | Team B',
    `${safe(node.team_a)} | ${safe(node.team_b)}`)}

${row('📈', 'Left BV | Right BV',
    `${safe(node.left_bv)} | ${safe(node.right_bv)}`)}

${row('📦', 'Total BV', safe(node.total_bv))}
            ${row('🏅', 'Designation', node.designation || 'Associate')}
            ${row('🛡', 'KYC', `<span style="font-size:10px;padding:1px 6px;border-radius:999px;background:#dbeafe;color:#1e40af;font-weight:500;">${node.kyc_status || 'Unverified'}</span>`)}
            ${row('📍', 'City', node.city || '—')}

        </div>
    `;

    const popupH = popup.offsetHeight;
    const popupW = popup.offsetWidth;
    const margin = 16;
    const viewH  = window.innerHeight;
    const viewW  = window.innerWidth;

    const rect = targetEl.getBoundingClientRect();

    // Try right side first, then left
    let left = rect.right + margin;
    let top  = rect.top + rect.height / 2 - popupH / 2;

    // If overflows right, place on left
    if (left + popupW > viewW - margin) {
        left = rect.left - popupW - margin;
    }

    // If overflows bottom, shift up
    if (top + popupH > viewH - margin) top = viewH - popupH - margin;
    if (top < margin) top = margin;
    if (left < margin) left = margin;

    popup.style.left = left + 'px';
    popup.style.top  = top  + 'px';
    popup.style.visibility = 'visible';
}

function hidePopup() {
    clearTimeout(hideTimer);
    hideTimer = setTimeout(() => {
        if (!isPopupHovered) {
            popup.style.display = 'none';
        }
    }, 250);
}

popup.addEventListener('mouseenter', () => {
    clearTimeout(hideTimer);
    isPopupHovered = true;
});
popup.addEventListener('mouseleave', () => {
    isPopupHovered = false;
    popup.style.display = 'none';
});

async function viewMember(userId) {
    try {
        const response = await fetch(`/api/admin/auto-login/${userId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        const data = await response.json();
        if (data.status) {
            localStorage.clear();
            localStorage.setItem("member_user_id", String(data.member.user_id));
            localStorage.setItem("memberData", JSON.stringify(data.member || {}));
            localStorage.setItem("memberSession", "true");
            window.open('/member/dashboard', '_blank');
        } else {
            alert(data.message || 'Login failed');
        }
    } catch (error) {
        console.error(error);
        alert('Something went wrong');
    }
}

function editMember(id) {
    window.open(`/admin/member-reports/${id}/edit`, '_blank');
}

/* ──────────────────────────────────────────────────────────────
   Tree loading + drill-down
   (ported from BuiltupTree.jsx: loadDefaultTree / loadTree / handleBack)
   ────────────────────────────────────────────────────────────── */

function renderTree(tree) {
    const treeDiv = document.getElementById('tree');
    treeDiv.innerHTML = '';

    if (!tree) {
        treeDiv.innerHTML = `<p style="text-align:center;color:red;">No Tree Found</p>`;
        updateBackButton();
        return;
    }

    const ul = document.createElement('ul');
    ul.appendChild(renderNode(tree));
    treeDiv.appendChild(ul);

    updateBackButton();
    requestAnimationFrame(centerTree);
}

// Initial load — full tree from the existing endpoint (unchanged API)
async function loadDefaultTree() {
    const treeDiv = document.getElementById('tree');
    try {
        treeDiv.innerHTML = `<p style="text-align:center;color:#999;">Loading full tree...</p>`;

        const res  = await fetch(`{{ url('/api/admin/tree') }}`);
        const data = await res.json();

        if (!data || !data.tree) {
            treeDiv.innerHTML = `<p style="text-align:center;color:red;">No Tree Found</p>`;
            return;
        }

        currentTree = data.tree;
        treeHistory = [];
        renderTree(currentTree);

    } catch (error) {
        console.error(error);
        treeDiv.innerHTML = `<p style="text-align:center;color:red;">Error loading tree</p>`;
    }
}

// Click a member -> dynamically expand that member's left/right downline
// by making it the new root. Multi-level: keep clicking to go deeper.
async function loadTree(userId) {
    if (!userId) return;

    try {
        const res  = await fetch(`{{ url('/api/admin/tree') }}?user_id=${encodeURIComponent(userId)}`);
        const data = await res.json();

        if (res.status === 403) {
            alert(data.message || 'You are not allowed to view this member.');
            return;
        }

        if (data && data.tree) {
            if (currentTree) treeHistory.push(currentTree);
            currentTree = data.tree;
            renderTree(currentTree);
        } else {
            alert('User not found');
        }
    } catch (err) {
        console.error(err);
        alert('Unable to load member tree');
    }
}

// Collapse back to the previous view (BuiltupTree.jsx handleBack)
function handleBack() {
    if (treeHistory.length === 0) return;
    currentTree = treeHistory.pop();
    renderTree(currentTree);
}

function updateBackButton() {
    const btn = document.getElementById('treeBackBtn');
    if (btn) btn.disabled = treeHistory.length === 0;
}

function renderNode(node) {
    if (!node) return null;

    const li = document.createElement('li');
    const nodeWrap = document.createElement('div');
    nodeWrap.className = 'node';

    const box = document.createElement('div');
    box.className = 'box ' + (isActive(node) ? 'active' : 'inactive');
    box.innerHTML = '👤';

    if (isActiveMember(node)) {
        box.style.cursor = 'pointer';
        box.addEventListener('mouseenter', (e) => showPopup(node, e.currentTarget));
        box.addEventListener('mouseleave', hidePopup);
    }

    const label = document.createElement('div');
    label.className = 'label';

    // user_id line = drill-down trigger (same as Member Panel Tree)
    const uid = document.createElement('div');
    uid.className = 'tree-uid';
    uid.innerHTML = `<b>${node.user_id}</b>`;
    uid.title = "Click to expand this member's downline";
    uid.addEventListener('click', () => loadTree(node.user_id));

    const name = document.createElement('div');
    name.textContent = node.fullname || '';

    label.appendChild(uid);
    label.appendChild(name);

    nodeWrap.appendChild(box);
    nodeWrap.appendChild(label);
    li.appendChild(nodeWrap);

    const children = [];
    if (node.left)  children.push(renderNode(node.left));
    if (node.right) children.push(renderNode(node.right));

    if (children.length > 0) {
        const ul = document.createElement('ul');
        children.forEach(child => { if (child) ul.appendChild(child); });
        li.appendChild(ul);
    }

    return li;
}

document.addEventListener('DOMContentLoaded', loadDefaultTree);
</script>

</x-filament::page>
