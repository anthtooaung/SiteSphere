document.addEventListener('DOMContentLoaded', () => {
  const data = window.AdminActivityData || {};
  const actsExpanded = data.actsExpanded || [];
  
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
    calYear = pickerYear;
    calMonth = monthIdx;
    renderCalendar();
    document.getElementById("cal-month-picker")?.classList.remove("open");
    document.getElementById("cal-month-btn")?.classList.remove("open");
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
    if(termLabel) termLabel.textContent = `Academic Term Year: ${calYear}`;

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
      const dotColor = isToday ? "rgba(255,255,255,.75)" : (entries && entries[0].color);
      const dotHtml = entries ? `<span class="cal-dot" style="background:${dotColor}"></span>` : "";
      
      const cls = ["cal-day", isToday && "today", isSel && "selected"].filter(Boolean).join(" ");
      html += `<div class="${cls}" data-date="${ds}">${d}${dotHtml}</div>`;
    }
    
    const grid = document.getElementById("cal-grid");
    if(grid) {
      grid.innerHTML = html;
      grid.querySelectorAll('.cal-day:not(.empty)').forEach(el => {
          el.addEventListener('click', (e) => {
              // Get the closest .cal-day in case a child element was clicked
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
          body.innerHTML = `<div class="alc-empty"><i class="fa-regular fa-calendar-xmark"></i><p>No admin actions on this day.</p></div>`;
          return;
        }
        
        const visible = entries.slice(0, 3);
        const hasMore = entries.length > 3;
        const rows = visible.map((a, i) => `
          <div class="alc-entry">
            <div class="alc-icon" style="background:${a.color}18"><i class="fa-solid fa-${a.icon}" style="color:${a.color}"></i></div>
            <div class="alc-info">
              <div class="alc-txt">${a.txt}</div>
              <div class="alc-time"><i class="fa-regular fa-clock"></i> ${a.time}</div>
            </div>
          </div>${i < visible.length - 1 ? '<div class="alc-divider"></div>' : ""}`
        ).join("");
        
        const seeMore = hasMore ? `<div class="alc-see-more" data-action="see-more"><i class="fa-solid fa-arrow-up-right-from-square"></i> See all ${entries.length} actions</div>` : "";
        body.innerHTML = rows + seeMore;
        
        const seeMoreBtn = body.querySelector('[data-action="see-more"]');
        if(seeMoreBtn) {
            seeMoreBtn.addEventListener('click', () => openLogModal('date'));
        }
    }
  }

  function calPrev() {
    if (--calMonth < 0) {
      calMonth = 11;
      calYear--;
    }
    renderCalendar();
  }
  
  function calNext() {
    if (++calMonth > 11) {
      calMonth = 0;
      calYear++;
    }
    renderCalendar();
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
          ? entries.map(a => `
            <div class="modal-row">
              <div class="modal-icon" style="background:${a.color}18"><i class="fa-solid fa-${a.icon}" style="color:${a.color}"></i></div>
              <div class="modal-info"><div class="tl-txt">${a.txt}</div><div class="tl-time"><i class="fa-regular fa-clock"></i> ${a.time}</div></div>
              <span class="modal-date-chip">${a.date}</span>
            </div>`
          ).join("")
          : '<div class="exp-empty" style="padding:40px 0"><i class="fa-regular fa-calendar-xmark"></i><p>No actions found.</p></div>';
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
  const btnPrev = document.querySelector('.cal-nav .fa-chevron-left')?.parentElement;
  if(btnPrev) btnPrev.addEventListener('click', calPrev);
  
  const btnNext = document.querySelector('.cal-nav .fa-chevron-right')?.parentElement;
  if(btnNext) btnNext.addEventListener('click', calNext);
  
  const monthBtn2 = document.getElementById("cal-month-btn");
  if(monthBtn2) monthBtn2.addEventListener('click', toggleMonthPicker);
  
  document.querySelectorAll('.cmp-ynav').forEach(btn => {
      btn.addEventListener('click', (e) => {
          const isNext = btn.querySelector('.fa-chevron-right');
          if(isNext) pickerNextYear(); else pickerPrevYear();
      });
  });

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
  
  const entryCount = document.getElementById("entry-count");
  if(entryCount) entryCount.textContent = `${actsExpanded.length} entries`;
});
