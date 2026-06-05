"use strict";

/* =========================================================================
   1. THREE-DOT MENU
   ========================================================================= */
(function initMenu() {
  const btn = document.getElementById("menuBtn");
  const dropdown = document.getElementById("menuDropdown");
  if (!btn || !dropdown) return;

  function open() {
    dropdown.classList.add("is-open");
    btn.classList.add("is-active");
    btn.setAttribute("aria-expanded", "true");
  }

  function close() {
    dropdown.classList.remove("is-open");
    btn.classList.remove("is-active");
    btn.setAttribute("aria-expanded", "false");
  }

  btn.addEventListener("click", (e) => {
    e.stopPropagation();
    dropdown.classList.contains("is-open") ? close() : open();
  });

  document.addEventListener("click", close);
})();

/* =========================================================================
   2. DEPOSITION TAB SWITCHING
   ========================================================================= */
(function initDepoTabs() {
  const nav = document.getElementById("depoNav");
  const panels = document.getElementById("depoPanels");
  if (!nav || !panels) return;

  const tabs = Array.from(nav.querySelectorAll(".aud-depo-tab"));
  const panelEls = Array.from(panels.querySelectorAll(".aud-depo-panel"));

  function activate(id) {
    tabs.forEach((tab) => {
      const isActive = tab.dataset.contributor === id;
      tab.classList.toggle("is-active", isActive);
      tab.setAttribute("aria-selected", String(isActive));
    });

    panelEls.forEach((panel) => {
      panel.hidden = panel.dataset.panel !== id;
    });
  }

  tabs.forEach((tab) => {
    tab.addEventListener("click", () => activate(tab.dataset.contributor));
  });
})();

/* =========================================================================
   3. EXPANDABLE TEXT — ResizeObserver-based overflow detection
   ========================================================================= */
function initExpandable(container) {
  const p = container.querySelector(".ss-expandable-text");
  const ghost = container.querySelector(".ss-expandable-ghost");
  const btn = container.querySelector(".ss-expand-btn");
  if (!p || !ghost || !btn) return;

  btn.addEventListener("click", () => {
    const isExpanded = container.classList.toggle("is-expanded");
    p.classList.toggle("is-open", isExpanded);

    if (isExpanded) {
      p.style.setProperty("-webkit-line-clamp", "unset");
      btn.textContent = "See less";
    } else {
      p.style.removeProperty("-webkit-line-clamp");
      btn.textContent = "See more";
    }
  });

  const ro = new ResizeObserver(() => {
    if (container.classList.contains("is-expanded")) return;

    if (ghost.offsetHeight > p.offsetHeight + 2) {
      btn.hidden = false;
    } else {
      btn.hidden = true;
    }
  });

  ro.observe(p);
}

document.querySelectorAll(".ss-expandable").forEach(initExpandable);

/* =========================================================================
   4. HELPFUL VOTE TOGGLE BUTTONS (AJAX Fetch API)
   ========================================================================= */
function initHelpfulBtn(btn) {
  const commentId = btn.dataset.commentId;
  if (!commentId) return;

  const countEl = btn.querySelector(".helpful-count");
  if (!countEl) return;

  btn.addEventListener("click", () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
      window.location.href = "/login";
      return;
    }

    btn.disabled = true;

    fetch(`/comments/${commentId}/react`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
        "Accept": "application/json"
      }
    })
    .then(res => {
      if (res.status === 401) {
        window.location.href = "/login";
        throw new Error("Unauthorized");
      }
      return res.json();
    })
    .then(data => {
      btn.disabled = false;
      const voted = data.voted;
      const count = data.helpful_count;

      btn.classList.toggle("is-active", voted);
      btn.setAttribute("aria-pressed", String(voted));

      const noun = count === 1 ? "person" : "people";
      countEl.textContent = `${count} ${noun} found this helpful`;
    })
    .catch(err => {
      btn.disabled = false;
      console.error(err);
    });
  });
}

document.querySelectorAll(".js-helpful-btn").forEach(initHelpfulBtn);

/* =========================================================================
   5. REVIEW COMPOSER — STAR PICKER
   ========================================================================= */
(function initComposer() {
  const form = document.getElementById("reviewForm");
  const picker = document.getElementById("ratingPicker");
  const textarea = document.getElementById("reviewTextarea");
  const submitBtn = document.getElementById("reviewSubmit");
  const ratingInput = document.getElementById("ratingInput");
  if (!form || !picker || !textarea || !submitBtn || !ratingInput) return;

  let currentRating = parseInt(ratingInput.value, 10) || 0;

  // Build interactive 5-star picker nodes
  for (let idx = 1; idx <= 5; idx++) {
    const starBtn = document.createElement("button");
    starBtn.type = "button";
    starBtn.className = "ss-pick-star";
    starBtn.setAttribute("role", "radio");
    starBtn.setAttribute("aria-checked", "false");
    starBtn.setAttribute("aria-label", `${idx} Star${idx > 1 ? "s" : ""}`);
    starBtn.innerHTML = `
      <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
        <path d="M12 2.4l2.95 5.98 6.6.96-4.78 4.66 1.13 6.57L12 17.5l-5.9 3.07 1.13-6.57L2.45 9.34l6.6-.96L12 2.4z"/>
      </svg>
    `;

    starBtn.addEventListener("click", () => {
      currentRating = idx;
      ratingInput.value = idx;
      updatePickerUI();
      validate();
    });

    picker.appendChild(starBtn);
  }

  function updatePickerUI() {
    const stars = picker.querySelectorAll(".ss-pick-star");
    stars.forEach((star, index) => {
      const active = index < currentRating;
      star.classList.toggle("is-on", active);
      star.setAttribute("aria-checked", String(active));
    });
  }

  function validate() {
    const hasText = textarea.value.trim().length > 0;
    const hasRating = currentRating > 0;
    submitBtn.disabled = !(hasText && hasRating);
  }

  textarea.addEventListener("input", validate);
  
  if (currentRating > 0) {
    updatePickerUI();
    validate();
  }
})();

/* =========================================================================
   6. LINKED RECORDS SCROLL ARROWS
   ========================================================================= */
(function initRelatedArrows() {
  const grid = document.getElementById("relatedGrid");
  const prevBtn = document.getElementById("relPrev");
  const nextBtn = document.getElementById("relNext");
  if (!grid || !prevBtn || !nextBtn) return;

  const SCROLL_AMOUNT = 280;

  prevBtn.addEventListener("click", () => {
    grid.scrollBy({ left: -SCROLL_AMOUNT, behavior: "smooth" });
  });
  nextBtn.addEventListener("click", () => {
    grid.scrollBy({ left: SCROLL_AMOUNT, behavior: "smooth" });
  });

  function updateArrows() {
    const atStart = grid.scrollLeft <= 0;
    const atEnd = grid.scrollLeft + grid.clientWidth >= grid.scrollWidth - 2;

    prevBtn.disabled = atStart;
    nextBtn.disabled = atEnd;
    prevBtn.classList.toggle("is-disabled", atStart);
    nextBtn.classList.toggle("is-disabled", atEnd);
  }

  grid.addEventListener("scroll", updateArrows, { passive: true });

  setTimeout(updateArrows, 100);
})();
