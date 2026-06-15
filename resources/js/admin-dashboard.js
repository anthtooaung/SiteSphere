document.addEventListener('DOMContentLoaded', () => {
  const data = window.AdminDashboardData || {};
  const categories = data.stats || [];
  const acts = data.recentActivity || [];
  const posts = data.topPosts || [];

  // ══ EXISTING PIE CHART (Admin Overview) ══════════════════════════════════
  const popup = document.getElementById("cat-popup");
  let catClickPending = false;
  if(popup) popup.style.display = "none";

  function showPopup(cat) {
    document.getElementById("popup-dot").style.background = cat.color;
    document.getElementById("popup-name").textContent = cat.name;
    document.getElementById("popup-kpi-val").textContent = cat.value.toLocaleString();
    document.getElementById("popup-kpi-trend").innerHTML = cat.trendHtml;
    const container = document.getElementById("infographic-pie-container");
    if(container && popup) {
      popup.style.top = container.offsetTop + container.offsetHeight + 10 + "px";
      popup.style.left = "50%";
      popup.style.transform = "translateX(-50%)";
      popup.style.display = "block";
    }
  }

  function hidePopup() {
    if(popup) popup.style.display = "none";
  }

  document.addEventListener("click", () => {
    if (catClickPending) {
      catClickPending = false;
      return;
    }
    hidePopup();
  });

  const catSlicePaths = [];
  (function buildCatSvg() {
    const svgEl = document.getElementById("cat-svg");
    const labelsEl = document.getElementById("cat-labels-overlay");
    if (!svgEl || !labelsEl || categories.length === 0) return;
    const NS = "http://www.w3.org/2000/svg";
    const VW = 400, VH = 400, CX = 200, CY = 200;
    const INNER_R = 72, MIN_OUTER = 120, MAX_OUTER = 170, GAP_DEG = 3.5;
    const logTotal = categories.reduce((s, c) => s + c.logVal, 0);
    const maxLogVal = Math.max(...categories.map((c) => c.logVal));
    const toRad = (a) => (a * Math.PI) / 180;
    const px = (r, a) => (CX + r * Math.cos(toRad(a))).toFixed(2);
    const py = (r, a) => (CY + r * Math.sin(toRad(a))).toFixed(2);

    function arcPath(r1, r2, sa, ea) {
      const lg = ea - sa > 180 ? 1 : 0;
      return `M${px(r2, sa)} ${py(r2, sa)} A${r2} ${r2} 0 ${lg} 1 ${px(r2, ea)} ${py(r2, ea)} L${px(r1, ea)} ${py(r1, ea)} A${r1} ${r1} 0 ${lg} 0 ${px(r1, sa)} ${py(r1, sa)} Z`;
    }

    function mkEl(tag, attrs) {
      const el = document.createElementNS(NS, tag);
      Object.entries(attrs).forEach(([k, v]) => el.setAttribute(k, v));
      return el;
    }

    let angle = -90;
    categories.forEach((cat) => {
      const pct = logTotal > 0 ? (cat.logVal / logTotal) : 0;
      const sweep = pct * 360;
      const ea = angle + sweep - GAP_DEG;
      const mid = angle + sweep / 2 - GAP_DEG / 2;
      const outerR = maxLogVal > 0 ? MIN_OUTER + (cat.logVal / maxLogVal) * (MAX_OUTER - MIN_OUTER) : MIN_OUTER;
      const path = mkEl("path", {
        d: arcPath(INNER_R, outerR, angle, ea),
        fill: cat.color,
      });
      path.style.cursor = "pointer";
      path.style.transition = "filter .15s, opacity .3s";
      path.addEventListener("mouseenter", () => {
        if (!path.classList.contains("slice-dimmed"))
          path.style.filter = "brightness(1.18)";
      });
      path.addEventListener("mouseleave", () => {
        path.style.filter = "";
      });
      path.addEventListener("click", () => {
        catClickPending = true;
        showPopup(cat);
      });
      svgEl.appendChild(path);
      catSlicePaths.push(path);

      const labelR = INNER_R + (outerR - INNER_R) * 0.58;
      const lxPct = ((CX + labelR * Math.cos(toRad(mid))) / VW) * 100;
      const lyPct = ((CY + labelR * Math.sin(toRad(mid))) / VH) * 100;
      const div = document.createElement("div");
      div.className = "cat-seg-label";
      div.style.left = lxPct + "%";
      div.style.top = lyPct + "%";
      div.innerHTML = `<span class="csl-icon"><i class="fa-solid ${cat.icon}"></i></span><span class="csl-pct">${cat.value >= 10000 ? (cat.value / 1000).toFixed(1) + "K" : cat.value.toLocaleString()}</span><span class="csl-name">${cat.name}</span>`;
      labelsEl.appendChild(div);
      angle += sweep;
    });

    svgEl.appendChild(
      mkEl("circle", {
        cx: CX,
        cy: CY,
        r: INNER_R - 5,
        fill: "#ffffff",
        stroke: "rgba(79,70,229,.15)",
        "stroke-width": "1.5",
      }),
    );
    [
      [CY - 17, "PLATFORM", 8.5, "700", "#94a3b8"],
      [CY + 2, "OVERVIEW", 16, "900", "#0f172a"],
      [CY + 18, "Jun 2026", 8.5, "500", "#94a3b8"],
    ].forEach(([y, txt, sz, wt, clr]) => {
      const t = mkEl("text", {
        x: CX,
        y,
        "text-anchor": "middle",
        "dominant-baseline": "middle",
        "font-size": sz,
        "font-weight": wt,
        fill: clr,
        "font-family": 'system-ui, -apple-system, "Segoe UI", sans-serif',
      });
      t.textContent = txt;
      svgEl.appendChild(t);
    });
  })();

  // ══ GENERALIZED PIE BUILDER ══════════════════════════════════════════════
  function buildOvPie(svgId, labelsId, innerR, minOuter, maxOuter, showLabels, showNames = true, centerStyle = "default") {
    const svgEl = document.getElementById(svgId);
    if (!svgEl || categories.length === 0) return [];
    const labelsEl = labelsId ? document.getElementById(labelsId) : null;
    const NS = "http://www.w3.org/2000/svg";
    const CX = 200, CY = 200, GAP_DEG = 3.5;
    const logTotal = categories.reduce((s, c) => s + c.logVal, 0);
    const maxLogVal = Math.max(...categories.map((c) => c.logVal));
    const toRad = (a) => (a * Math.PI) / 180;
    const px = (r, a) => (CX + r * Math.cos(toRad(a))).toFixed(2);
    const py = (r, a) => (CY + r * Math.sin(toRad(a))).toFixed(2);

    function arcPath(r1, r2, sa, ea) {
      const lg = ea - sa > 180 ? 1 : 0;
      return `M${px(r2, sa)} ${py(r2, sa)} A${r2} ${r2} 0 ${lg} 1 ${px(r2, ea)} ${py(r2, ea)} L${px(r1, ea)} ${py(r1, ea)} A${r1} ${r1} 0 ${lg} 0 ${px(r1, sa)} ${py(r1, sa)} Z`;
    }

    function mkEl(tag, attrs) {
      const el = document.createElementNS(NS, tag);
      Object.entries(attrs).forEach(([k, v]) => el.setAttribute(k, v));
      return el;
    }

    const slicePaths = [];
    let angle = -90;
    categories.forEach((cat) => {
      const pct = logTotal > 0 ? (cat.logVal / logTotal) : 0;
      const sweep = pct * 360;
      const ea = angle + sweep - GAP_DEG;
      const mid = angle + sweep / 2 - GAP_DEG / 2;
      const outerR = maxLogVal > 0 ? minOuter + (cat.logVal / maxLogVal) * (maxOuter - minOuter) : minOuter;
      const path = mkEl("path", {
        d: arcPath(innerR, outerR, angle, ea),
        fill: cat.color,
      });
      path.style.cursor = "pointer";
      path.style.transition = "filter .15s, opacity .3s";
      path.addEventListener("mouseenter", () => {
        if (!path.classList.contains("slice-dimmed"))
          path.style.filter = "brightness(1.18)";
      });
      path.addEventListener("mouseleave", () => {
        path.style.filter = "";
      });
      svgEl.appendChild(path);
      slicePaths.push(path);
      if (showLabels && labelsEl) {
        const labelR = innerR + (outerR - innerR) * 0.55;
        const lxPct = ((CX + labelR * Math.cos(toRad(mid))) / 400) * 100;
        const lyPct = ((CY + labelR * Math.sin(toRad(mid))) / 400) * 100;
        const div = document.createElement("div");
        div.className = "cat-seg-label";
        div.style.left = lxPct + "%";
        div.style.top = lyPct + "%";
        div.innerHTML = `<span class="csl-icon"><i class="fa-solid ${cat.icon}"></i></span><span class="csl-pct">${cat.value >= 10000 ? (cat.value / 1000).toFixed(1) + "K" : cat.value.toLocaleString()}</span>${showNames ? `<span class="csl-name">${cat.name}</span>` : ""}`;
        labelsEl.appendChild(div);
      }
      angle += sweep;
    });

    svgEl.appendChild(
      mkEl("circle", {
        cx: CX,
        cy: CY,
        r: innerR - 5,
        fill: "#ffffff",
        stroke: "rgba(79,70,229,.15)",
        "stroke-width": "1.5",
      }),
    );

    if (showLabels) {
      if (centerStyle === "total") {
        const totalVal = categories.reduce((s, c) => s + c.value, 0);
        const totalFmt = totalVal >= 10000 ? (totalVal / 1000).toFixed(1) + "K" : totalVal.toLocaleString();
        [
          [CY - 8, totalFmt, 20, "900", "#0f172a"],
          [CY + 12, "TOTAL ACTIVE", 7.5, "700", "#94a3b8"],
        ].forEach(([y, txt, sz, wt, clr]) => {
          const t = mkEl("text", {
            x: CX,
            y,
            "text-anchor": "middle",
            "dominant-baseline": "middle",
            "font-size": sz,
            "font-weight": wt,
            fill: clr,
            "font-family": 'system-ui,-apple-system,"Segoe UI",sans-serif',
          });
          t.textContent = txt;
          svgEl.appendChild(t);
        });
      } else {
        [
          [CY - 17, "PLATFORM", 8.5, "700", "#94a3b8"],
          [CY + 2, "OVERVIEW", 16, "900", "#0f172a"],
          [CY + 18, "Jun 2026", 8.5, "500", "#94a3b8"],
        ].forEach(([y, txt, sz, wt, clr]) => {
          const t = mkEl("text", {
            x: CX,
            y,
            "text-anchor": "middle",
            "dominant-baseline": "middle",
            "font-size": sz,
            "font-weight": wt,
            fill: clr,
            "font-family": 'system-ui,-apple-system,"Segoe UI",sans-serif',
          });
          t.textContent = txt;
          svgEl.appendChild(t);
        });
      }
    }
    return slicePaths;
  }

  // Build pie for layout 9
  const catSlicePaths9 = buildOvPie("cat-svg-9", "cat-labels-9", 95, 138, 182, true, false, "total");

  // ── FILTER: Layout 9 ─────────────────────────────────────────────────────
  document.querySelectorAll(".ov9-kpi[data-ov9-idx]").forEach((card) => {
    card.addEventListener("click", () => {
      const idx = parseInt(card.dataset.ov9Idx);
      const isActive = card.classList.contains("kpi-active");
      document.querySelectorAll(".ov9-kpi[data-ov9-idx]").forEach((c) => c.classList.remove("kpi-active", "kpi-dimmed"));
      catSlicePaths9.forEach((p) => {
        p.style.opacity = "1";
        p.classList.remove("slice-dimmed");
      });
      if (!isActive) {
        card.classList.add("kpi-active");
        document.querySelectorAll(".ov9-kpi[data-ov9-idx]").forEach((c) => {
          if (c !== card) c.classList.add("kpi-dimmed");
        });
        catSlicePaths9.forEach((p, i) => {
          if (i !== idx) {
            p.style.opacity = "0.15";
            p.classList.add("slice-dimmed");
          }
        });
      }
    });
  });

  // ══ ACTIVITY FEED ═══════════════════════════════════════════════════════
  const activityList = document.getElementById("activity-list");
  if(activityList) {
    if(acts.length === 0) {
        activityList.innerHTML = `<div class="tl-item"><div class="tl-txt" style="color:var(--muted)">No recent activity found.</div></div>`;
    } else {
        activityList.innerHTML = acts.map(a =>
          `<div class="tl-item"><i class="fa-solid fa-${a.icon} tl-dot" style="color:${a.color}"></i><div class="tl-txt">${a.txt}</div><div class="tl-time"><i class="fa-regular fa-clock"></i> ${a.time}</div></div>`
        ).join("");
    }
  }

  // ══ TOP POSTS ═══════════════════════════════════════════════════════════
  function starIcons(r) {
    let s = "";
    for (let i = 1; i <= 5; i++)
      s += `<i class="fa-${i <= r ? "solid" : "regular"} fa-star" style="color:${i <= r ? "#f59e0b" : "#cbd5e1"};font-size:13px"></i>`;
    return s;
  }
  const topPostsEl = document.getElementById("top-posts");
  if(topPostsEl) {
    if(posts.length === 0) {
        topPostsEl.innerHTML = `<div class="rank-item" style="color:var(--muted)">No top posts found.</div>`;
    } else {
        const numClass = ["gold", "silver", "bronze", "", ""];
        topPostsEl.innerHTML = posts.map((p, i) =>
          `<div class="rank-item"><div class="rank-num ${numClass[i] || ''}">${i + 1}</div><div style="flex:1;min-width:0"><div class="rank-title">${p.title}</div><div class="rank-sub">${starIcons(p.rating)}<span style="color:#cbd5e1">·</span><i class="fa-regular fa-comment" style="font-size:13px;color:#94a3b8"></i><span style="font-size:13px;color:#94a3b8">${p.comments}</span></div></div></div>`
        ).join("");
    }
  }

  // ══ MONTH PICKER ════════════════════════════════════════════════════════
  let ovYear = 2026, ovMonth = 5, ovPickerYear = 2026;
  
  const monthBtn = document.getElementById("overview-month-btn");
  if(monthBtn) {
      monthBtn.addEventListener("click", () => {
          const picker = document.getElementById("overview-month-picker");
          const isOpen = picker.classList.toggle("open");
          monthBtn.classList.toggle("open", isOpen);
          if (isOpen) {
            ovPickerYear = ovYear;
            renderOverviewPicker();
          }
      });
  }

  function renderOverviewPicker() {
    const yrEl = document.getElementById("overview-picker-year");
    if(yrEl) yrEl.textContent = ovPickerYear;
    
    const grid = document.getElementById("overview-picker-month-grid");
    if(grid) {
        const abbr = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        grid.innerHTML = abbr.map((m, i) =>
          `<div class="cmp-month${ovPickerYear === ovYear && i === ovMonth ? " active" : ""}" data-month-idx="${i}">${m}</div>`
        ).join("");
        
        grid.querySelectorAll('.cmp-month').forEach(el => {
            el.addEventListener('click', (e) => {
                selectOverviewMonth(parseInt(e.target.getAttribute('data-month-idx')));
            });
        });
    }
  }

  // Bind prev/next year buttons
  document.querySelectorAll('.cmp-ynav').forEach(btn => {
      btn.addEventListener('click', (e) => {
          const isNext = btn.querySelector('.fa-chevron-right');
          if(isNext) ovPickerYear++; else ovPickerYear--;
          renderOverviewPicker();
      });
  });

  function selectOverviewMonth(idx) {
    ovYear = ovPickerYear;
    ovMonth = idx;
    const abbr = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const label = document.getElementById("overview-month-label");
    if(label) label.textContent = `${abbr[idx]} ${ovPickerYear}`;
    
    document.getElementById("overview-month-picker")?.classList.remove("open");
    document.getElementById("overview-month-btn")?.classList.remove("open");
  }

  document.addEventListener("click", (e) => {
    const wrap = document.querySelector(".overview-month-wrap");
    if (wrap && !wrap.contains(e.target)) {
      document.getElementById("overview-month-picker")?.classList.remove("open");
      document.getElementById("overview-month-btn")?.classList.remove("open");
    }
  });

});
