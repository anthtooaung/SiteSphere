document.addEventListener('DOMContentLoaded', () => {
  const data = window.AdminActivityData || {};
  const actsExpanded = data.actsExpanded || [];
  const postSlugs = data.postSlugs || {};
  const userSlugs = data.userSlugs || {};
  const commentPostSlugs = data.commentPostSlugs || {};

  function getTargetUrl(a) {
    if (a.targetType === 'App\\Models\\Posts') {
      const slug = postSlugs[a.targetId];
      return slug ? `/posts/${slug}` : null;
    }
    if (a.targetType === 'App\\Models\\User') {
      const slug = userSlugs[a.targetId];
      return slug ? `/profile/${slug}` : null;
    }
    if (a.targetType === 'App\\Models\\Comments') {
      const postSlug = commentPostSlugs[a.targetId];
      return postSlug ? `/posts/${postSlug}#comment-${a.targetId}` : null;
    }
    return null;
  }

  let calYear = data.selectedYear || new Date().getFullYear();
  let calMonth = data.selectedMonth ? data.selectedMonth - 1 : new Date().getMonth();
  let selectedDate = data.selectedDate || new Date().toISOString().split('T')[0];
  let pickerYear = calYear;

  function buildDateMap() {
    const map = {};
    actsExpanded.forEach((a) => {
      (map[a.date] = map[a.date] || []).push(a);
    });
    return map;
  }

  function toggleMonthPicker() {
    const btn = document.getElementById("cal-month-btn");
    const picker = document.getElementById("cal-month-picker");
    if(!btn || !picker) return;
    const isOpen = picker.classList.toggle("open");
    btn.classList.toggle("open", isOpen);
    if (isOpen) {
      pickerYear = calYear;
      renderMonthPicker();
    }
  }

  function renderMonthPicker() {
    const pickerYearEl = document.getElementById("picker-year");
    if(pickerYearEl) pickerYearEl.textContent = pickerYear;
    const abbr = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const grid = document.getElementById("picker-month-grid");
    if(grid) {
      grid.innerHTML = abbr.map((m, i) =>
        `<div class="cmp-month${pickerYear === calYear && i === calMonth ? " active" : ""}" data-month="${i}">${m}</div>`
      ).join("");
      
      grid.querySelectorAll('.cmp-month').forEach(el => {
          el.addEventListener('click', (e) => {
              selectMonthYear(parseInt(e.target.getAttribute('data-month')));
          });
      });
    }
  }

  function pickerPrevYear() {
    pickerYear--;
    renderMonthPicker();
  }
  
  function pickerNextYear() {
    pickerYear++;
    renderMonthPicker();
  }

  function selectMonthYear(monthIdx) {
    window.location.href = `/menu/dashboard/activity-log?month=${monthIdx + 1}&year=${pickerYear}`;
  }

  document.addEventListener("click", (e) => {
    const mid = document.querySelector(".cal-header-mid");
    if (mid && !mid.contains(e.target)) {
      document.getElementById("cal-month-picker")?.classList.remove("open");
      document.getElementById("cal-month-btn")?.classList.remove("open");
    }
  });

  function renderCalendar() {
    const dateMap = buildDateMap();
    const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    const monthLabel = document.getElementById("cal-month-label");
    if(monthLabel) monthLabel.textContent = `${months[calMonth]} ${calYear}`;
    
    const termLabel = document.querySelector(".cal-term-label");
    if(termLabel) termLabel.textContent = `Platform Activity: ${months[calMonth]} ${calYear}`;

    const firstDow = new Date(calYear, calMonth, 1).getDay();
    const daysInMo = new Date(calYear, calMonth + 1, 0).getDate();
    // Use the actual today date for visual indication
    const todayStr = new Date().toLocaleDateString('en-CA', { year: 'numeric', month: '2-digit', day: '2-digit' }).replace(/\//g, '-');
    
    const dows = ["SUN", "MON", "TUE", "WED", "THU", "FRI", "SAT"];
    let html = dows.map((d) => `<div class="cal-dow">${d}</div>`).join("");
    
    for (let i = 0; i < firstDow; i++) {
      html += '<div class="cal-day empty"></div>';
    }
    
    for (let d = 1; d <= daysInMo; d++) {
      const ds = `${calYear}-${String(calMonth + 1).padStart(2, "0")}-${String(d).padStart(2, "0")}`;
      const entries = dateMap[ds];
      const isToday = ds === todayStr;
      const isSel = ds === selectedDate;
      const isFuture = ds > todayStr;
      
      const dotColor = isToday ? "rgba(255,255,255,.75)" : (entries && entries[0].color);
      const dotHtml = entries ? `<span class="cal-dot" style="background:${dotColor}"></span>` : "";
      
      const cls = [
          "cal-day", 
          isToday && "today", 
          isSel && "selected", 
          isFuture && "disabled",
          entries && "has-logs"
      ].filter(Boolean).join(" ");
      
      html += `<div class="${cls}" data-date="${ds}" ${isFuture ? 'title="Future dates unavailable"' : ''}>${d}${dotHtml}</div>`;
    }
    
    const grid = document.getElementById("cal-grid");
    if(grid) {
      grid.innerHTML = html;
      grid.querySelectorAll('.cal-day:not(.empty):not(.disabled)').forEach(el => {
          el.addEventListener('click', (e) => {
              const target = e.target.closest('.cal-day');
              if (target) {
                  selectDate(target.getAttribute('data-date'));
              }
          });
      });
    }
  }

  function selectDate(ds) {
    selectedDate = ds;
    renderCalendar();
    renderDatePanel();
  }

  function fmtSelectedDate(ds) {
    // Parse the date safely by splitting the string to avoid timezone issues
    const [y, m, d] = ds.split('-');
    const dateObj = new Date(y, m - 1, d);
    const days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    const mons = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    return {
      weekday: days[dateObj.getDay()],
      full: `${dateObj.getDate()} ${mons[dateObj.getMonth()]} ${dateObj.getFullYear()}`,
    };
  }

  function renderDatePanel() {
    const dateMap = buildDateMap();
    const entries = dateMap[selectedDate] || [];
    const fmt = fmtSelectedDate(selectedDate);
    
    const weekdayEl = document.getElementById("alc-weekday");
    if(weekdayEl) weekdayEl.textContent = fmt.weekday;
    
    const dateBigEl = document.getElementById("alc-date-big");
    if(dateBigEl) dateBigEl.textContent = fmt.full;
    
    const badge = document.getElementById("alc-badge");
    if(badge) {
        if (entries.length) {
          badge.textContent = `${entries.length} action${entries.length > 1 ? "s" : ""}`;
          badge.className = "alc-badge";
        } else {
          badge.textContent = "No actions";
          badge.className = "alc-badge zero";
        }
    }
    
    const body = document.getElementById("alc-body");
    if(body) {
        if (!entries.length) {
          body.innerHTML = `<div class="alc-empty"><p>No admin actions on this day.</p></div>`;
          return;
        }
        
        const visible = entries.slice(0, 3);
        const hasMore = entries.length > 3;
        const rows = visible.map((a, i) => {
          const targetUrl = getTargetUrl(a);
          const entryTag = targetUrl ? 'a' : 'div';
          const hrefAttr = targetUrl ? ` href="${targetUrl}"` : '';
          const clickableClass = targetUrl ? ' alc-entry--clickable' : '';
          return `<${entryTag} class="alc-entry${clickableClass}"${hrefAttr}>
            <div class="alc-icon" style="background:${a.color}18;"><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${a.color};"></span></div>
            <div class="alc-info">
              <div class="alc-txt">${a.txt}</div>
              <div class="alc-time"><i class="fa-regular fa-user"></i> ${a.user} <span style="margin:0 4px;opacity:0.5">·</span> <i class="fa-regular fa-clock"></i> ${a.timeAbsolute || a.time}</div>
            </div>
          </${entryTag}>${i < visible.length - 1 ? '<div class="alc-divider"></div>' : ""}`;
        }).join("");
        
        const seeMore = hasMore ? `<div class="alc-see-more" data-action="see-more">See all ${entries.length} actions &#8599;</div>` : "";
        body.innerHTML = rows + seeMore;
        
        const seeMoreBtn = body.querySelector('[data-action="see-more"]');
        if(seeMoreBtn) {
            seeMoreBtn.addEventListener('click', () => openLogModal('date'));
        }
    }
  }

  function calPrev() {
    let m = calMonth;
    let y = calYear;
    if (--m < 0) {
      m = 11;
      y--;
    }
    window.location.href = `/menu/dashboard/activity-log?month=${m + 1}&year=${y}`;
  }
  
  function calNext() {
    let m = calMonth;
    let y = calYear;
    if (++m > 11) {
      m = 0;
      y++;
    }
    window.location.href = `/menu/dashboard/activity-log?month=${m + 1}&year=${y}`;
  }

  function openLogModal(scope) {
    const dateMap = buildDateMap();
    const isDate = scope === "date";
    const entries = isDate ? dateMap[selectedDate] || [] : actsExpanded;
    const label = isDate ? `Actions on ${selectedDate}` : "All Admin Actions";
    
    const titleEl = document.getElementById("modal-title");
    if(titleEl) titleEl.textContent = "Detailed Admin Log";
    
    const subEl = document.getElementById("modal-sub");
    if(subEl) subEl.textContent = `${label} — ${entries.length} ${entries.length === 1 ? "entry" : "entries"}`;
    
    const mBody = document.getElementById("modal-body");
    if(mBody) {
        mBody.innerHTML = entries.length
          ? entries.map(a => {
            const targetUrl = getTargetUrl(a);
            const rowTag = targetUrl ? 'a' : 'div';
            const hrefAttr = targetUrl ? ` href="${targetUrl}"` : '';
            const clickableClass = targetUrl ? ' modal-row--clickable' : '';
            return `<${rowTag} class="modal-row${clickableClass}"${hrefAttr}>
              <div class="modal-icon" style="background:${a.color}18; color:${a.color};"><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${a.color};"></span></div>
              <div class="modal-info"><div class="tl-txt">${a.txt}</div><div class="tl-time"><i class="fa-regular fa-user"></i> ${a.user} <span style="margin:0 4px;opacity:0.5">·</span> ${a.timeAbsolute || a.time}</div></div>
              <span class="modal-date-chip">${a.date}</span>
            </${rowTag}>`;
          }).join("")
          : '<div class="exp-empty" style="padding:40px 0"><p>No actions found.</p></div>';
    }
    const modal = document.getElementById("log-modal");
    if(modal) {
        modal.classList.add("open");
        document.body.style.overflow = "hidden";
    }
  }

  function closeLogModal(e) {
    const modal = document.getElementById("log-modal");
    if (e && e.target !== modal) return;
    if(modal) modal.classList.remove("open");
    document.body.style.overflow = "";
  }

  // Bind static elements
  const btnPrev = document.getElementById('cal-prev');
  if(btnPrev) btnPrev.addEventListener('click', calPrev);
  
  const btnNext = document.getElementById('cal-next');
  if(btnNext) btnNext.addEventListener('click', calNext);
  
  const monthBtn2 = document.getElementById("cal-month-btn");
  if(monthBtn2) monthBtn2.addEventListener('click', toggleMonthPicker);
  
  const pickerPrevYearBtn = document.getElementById('picker-prev-year');
  if(pickerPrevYearBtn) pickerPrevYearBtn.addEventListener('click', pickerPrevYear);

  const pickerNextYearBtn = document.getElementById('picker-next-year');
  if(pickerNextYearBtn) pickerNextYearBtn.addEventListener('click', pickerNextYear);

  const modalCloseBtn = document.querySelector('.modal-close');
  if(modalCloseBtn) modalCloseBtn.addEventListener('click', (e) => {
      const modal = document.getElementById('log-modal');
      if(modal) modal.classList.remove('open');
      document.body.style.overflow = '';
  });
  
  const modalObj = document.getElementById('log-modal');
  if(modalObj) {
      modalObj.addEventListener('click', closeLogModal);
  }

  // Initialize
  renderCalendar();
  renderDatePanel();
});
