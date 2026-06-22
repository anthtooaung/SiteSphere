const initUploadPost = () => {
  const page = document.getElementById("uploadPostPage");

  if (!page) {
    return;
  }

  const categories = window.uploadPostCategories || {};
  const form = document.getElementById("uploadPostForm");
  const titleInput = document.getElementById("input-title");
  const linkInput = document.getElementById("input-link");
  const descInput = document.getElementById("input-desc");
  const tagTrigger = document.getElementById("tag-trigger-box");
  const tagTooltip = document.getElementById("tag-outside-tooltip");
  const tagSearchInput = document.getElementById("tag-search-input");
  const closeTagTooltipButton = document.getElementById("closeTagTooltip");
  const suggestedTagsContainer = document.getElementById("suggested-tags-container");
  const selectedTagsPreview = document.getElementById("form-active-tags-preview");
  const selectedTagsInputs = document.getElementById("selected-tags-inputs");
  const tagPlaceholder = document.getElementById("input-placeholder-text");
  const previewWrapper = document.getElementById("preview-wrapper-column");
  const previewCard = document.querySelector("[data-upload-preview-card]");
  const submitButton = document.getElementById("submit-main-btn");
  const discardButton = document.getElementById("discard-main-btn");
  const categoryButtons = Array.from(document.querySelectorAll("[data-category-button]"));

  if (!form || !titleInput || !linkInput || !descInput || !tagTrigger) {
    return;
  }

  let currentCategory = window.uploadPostInitialCategory || Object.keys(categories)[0] || null;
  let selectedTags = Array.isArray(window.uploadPostOldTags)
    ? window.uploadPostOldTags.map((tag) => String(tag))
    : [];

  const getCategoryTags = () => categories[currentCategory]?.tags || [];

  const isValidUrl = (value) => {
    try {
      const url = new URL(value);

      return url.protocol === "http:" || url.protocol === "https:";
    } catch {
      return false;
    }
  };

  const setFieldError = (field, hasError) => {
    const error = document.getElementById(`error-${field}`);
    const input = field === "tag" ? tagTrigger : document.getElementById(`input-${field}`);

    error?.classList.toggle("hidden", !hasError);
    input?.classList.toggle("is-invalid", hasError);
  };

  const clearFieldError = (field) => setFieldError(field, false);

  const setPreviewReady = (isReady) => {
    form.dataset.previewReady = isReady ? "true" : "false";
    previewWrapper?.classList.toggle("is-visible", isReady);
    submitButton?.classList.toggle("is-publish-ready", isReady);

    if (submitButton) {
      submitButton.textContent = isReady ? "Publish Post" : "Submit Content";
    }
  };

  const selectedTagRecords = () => {
    const seen = new Set();
    return Object.values(categories)
      .flatMap((category) => category.tags || [])
      .filter((tag) => {
        const idStr = String(tag.id);
        if (selectedTags.includes(idStr) && !seen.has(idStr)) {
          seen.add(idStr);
          return true;
        }
        return false;
      });
  };

  const syncSelectedTags = () => {
    if (!selectedTagsPreview || !selectedTagsInputs) {
      return;
    }

    selectedTagsPreview.innerHTML = "";
    selectedTagsInputs.innerHTML = "";

    const records = selectedTagRecords();
    tagPlaceholder?.classList.toggle("hidden", records.length > 0);

    records.forEach((tag) => {
      const pill = document.createElement("span");
      pill.className = "selected-tag-pill";
      pill.textContent = tag.name;

      const activeColor = tag.color || '#6c5ce7';
      pill.style.backgroundColor = `color-mix(in srgb, ${activeColor} 8%, var(--background-color, #ffffff))`;
      pill.style.color = activeColor;

      const removeButton = document.createElement("button");
      removeButton.type = "button";
      removeButton.textContent = "x";
      removeButton.setAttribute("aria-label", `Remove ${tag.name}`);
      removeButton.addEventListener("click", (event) => {
        event.stopPropagation();
        toggleTag(String(tag.id));
      });

      pill.appendChild(removeButton);
      selectedTagsPreview.appendChild(pill);

      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "tags[]";
      input.value = String(tag.id);
      selectedTagsInputs.appendChild(input);
    });
  };

  const renderCategoryButtons = () => {
    categoryButtons.forEach((button) => {
      const isActive = button.dataset.categoryButton === currentCategory;
      button.classList.toggle("is-active", isActive);

      const color = button.dataset.categoryColor || '#6c5ce7';
      if (isActive) {
        button.style.backgroundColor = color;
        button.style.color = '#ffffff';
      } else {
        button.style.backgroundColor = `color-mix(in srgb, ${color} 10%, var(--background-color, #ffffff))`;
        button.style.color = color;
      }
    });
  };

  const renderSuggestedTags = () => {
    if (!suggestedTagsContainer) {
      return;
    }

    const query = (tagSearchInput?.value || "").trim().toLowerCase();
    suggestedTagsContainer.innerHTML = "";

    getCategoryTags()
      .filter((tag) => !query || tag.name.toLowerCase().includes(query))
      .forEach((tag) => {
        const button = document.createElement("button");
        button.type = "button";
        button.className = "suggested-tag-button";
        button.textContent = tag.name;

        const isSelected = selectedTags.includes(String(tag.id));
        button.classList.toggle("is-selected", isSelected);

        const activeColor = tag.color || '#6c5ce7';
        if (isSelected) {
          button.style.backgroundColor = activeColor;
          button.style.color = '#ffffff';
          button.style.boxShadow = `0 0 0 2px var(--background-color, #ffffff), 0 0 0 4px ${activeColor}`;
        } else {
          button.style.backgroundColor = `color-mix(in srgb, ${activeColor} 8%, var(--background-color, #ffffff))`;
          button.style.color = activeColor;
          button.style.boxShadow = 'none';
        }

        button.addEventListener("click", (event) => {
          event.stopPropagation();
          toggleTag(String(tag.id));
        });

        suggestedTagsContainer.appendChild(button);
      });

    if (!suggestedTagsContainer.children.length) {
      const emptyState = document.createElement("p");
      emptyState.className = "tag-empty-state";
      emptyState.textContent = "No tags found.";
      suggestedTagsContainer.appendChild(emptyState);
    }
  };

  const updatePreview = () => {
    if (!previewCard) {
      return;
    }

    const title = titleInput.value.trim() || "Untitled Post";
    const link = linkInput.value.trim() || "https://example.com";
    const description = descInput.value.trim() || "No description written yet.";
    const titleElement = previewCard.querySelector("[data-post-card-title]");
    const urlElement = previewCard.querySelector("[data-post-card-url]");
    const linkElement = previewCard.querySelector("[data-post-card-link]");
    const tagsElement = previewCard.querySelector("[data-post-card-tags]");
    const descriptionElement = previewCard.querySelector("[data-post-card-description]");

    if (titleElement) {
      titleElement.textContent = title;
    }

    if (urlElement) {
      urlElement.textContent = link;
    }

    if (linkElement) {
      linkElement.setAttribute("href", isValidUrl(link) ? link : "#");
    }

    if (tagsElement) {
      const records = selectedTagRecords();
      tagsElement.innerHTML = "";

      if (!records.length) {
        tagsElement.appendChild(createPreviewTagPill("No tags selected", true));
      } else {
        records.forEach((tag) => tagsElement.appendChild(createPreviewTagPill(tag.name, false, tag.color)));
      }
    }

    if (descriptionElement) {
      descriptionElement.textContent = description;
    }
  };

  const createPreviewTagPill = (label, isEmpty = false, color = null) => {
    const pill = document.createElement("span");
    const dot = document.createElement("span");

    const activeColor = color || "var(--accent-color,#6c5ce7)";

    pill.className = "inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-[11px] font-bold transition-all";
    if (isEmpty) {
      pill.style.borderColor = "color-mix(in srgb, var(--text-color,#0d1b2a) 16%, transparent)";
      pill.style.backgroundColor = "color-mix(in srgb, var(--background-color,#ffffff) 94%, var(--text-color,#0d1b2a) 6%)";
      pill.style.color = "color-mix(in srgb, var(--text-color,#0d1b2a) 62%, transparent)";
      dot.className = "size-1.5 rounded-full [background:color-mix(in_srgb,var(--text-color,#0d1b2a)_42%,transparent)]";
    } else {
      pill.style.borderColor = `color-mix(in srgb, ${activeColor} 24%, var(--background-color,#ffffff))`;
      pill.style.backgroundColor = `color-mix(in srgb, var(--background-color,#ffffff) 90%, ${activeColor} 10%)`;
      pill.style.color = activeColor;
      dot.className = "size-1.5 rounded-full";
      dot.style.backgroundColor = activeColor;
    }

    pill.appendChild(dot);
    pill.append(label);

    return pill;
  };

  const validateBeforePreview = () => {
    const title = titleInput.value.trim();
    const link = linkInput.value.trim();
    const description = descInput.value.trim();

    setFieldError("title", !title);
    setFieldError("link", !link || !isValidUrl(link));
    setFieldError("desc", !description);
    setFieldError("tag", selectedTags.length === 0);

    return Boolean(title && isValidUrl(link) && description && selectedTags.length > 0);
  };

  function toggleTag(tagId) {
    selectedTags = selectedTags.includes(tagId)
      ? selectedTags.filter((id) => id !== tagId)
      : [...selectedTags, tagId];

    clearFieldError("tag");
    syncSelectedTags();
    renderSuggestedTags();
    updatePreview();
  }

  const openTagTooltip = () => {
    tagTooltip?.classList.remove("is-hidden");
    tagSearchInput?.focus();
  };

  const closeTagTooltip = () => {
    tagTooltip?.classList.add("is-hidden");
  };

  categoryButtons.forEach((button) => {
    button.addEventListener("click", () => {
      currentCategory = button.dataset.categoryButton;
      renderCategoryButtons();
      renderSuggestedTags();
      updatePreview();
    });
  });

  tagTrigger.addEventListener("click", openTagTooltip);
  closeTagTooltipButton?.addEventListener("click", closeTagTooltip);
  tagSearchInput?.addEventListener("input", renderSuggestedTags);

  document.addEventListener("click", (event) => {
    if (!tagTooltip || tagTooltip.classList.contains("is-hidden")) {
      return;
    }

    if (!tagTooltip.contains(event.target) && !tagTrigger.contains(event.target)) {
      closeTagTooltip();
    }
  });

  [titleInput, linkInput, descInput].forEach((input) => {
    input.addEventListener("input", () => {
      clearFieldError(input === titleInput ? "title" : input === linkInput ? "link" : "desc");

      if (form.dataset.previewReady === "true") {
        updatePreview();
      }
    });
  });

  discardButton?.addEventListener("click", async () => {
    const hasData = titleInput.value.trim() !== "" || 
                    linkInput.value.trim() !== "" || 
                    descInput.value.trim() !== "" || 
                    selectedTags.length > 0;

    const doDiscard = () => {
      form.reset();
      selectedTags = [];
      currentCategory = window.uploadPostInitialCategory || Object.keys(categories)[0] || null;
      ["title", "link", "desc", "tag"].forEach(clearFieldError);
      setPreviewReady(false);
      renderCategoryButtons();
      syncSelectedTags();
      renderSuggestedTags();
      updatePreview();
      closeTagTooltip();
      
      if (window.history.length > 1) {
        window.history.back();
      } else {
        window.location.href = "/";
      }
    };

    if (!hasData) {
      doDiscard();
    } else {
      const result = await window.sitesphereSwal.confirm({
        title: "Discard Post?",
        text: "Are you sure you want to discard your progress? This action cannot be undone.",
        confirmButtonText: "Yes, discard it",
        cancelButtonText: "Cancel"
      });

      if (result.isConfirmed) {
        doDiscard();
      }
    }
  });

  form.addEventListener("submit", (event) => {
    if (form.dataset.previewReady === "true") {
      return;
    }

    event.preventDefault();

    if (!validateBeforePreview()) {
      return;
    }

    closeTagTooltip();
    syncSelectedTags();
    updatePreview();
    setPreviewReady(true);

    if (window.innerWidth < 1024) {
      setTimeout(() => previewWrapper?.scrollIntoView({ behavior: "smooth", block: "start" }), 300);
    }
  });

  renderCategoryButtons();
  syncSelectedTags();
  renderSuggestedTags();
  updatePreview();
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initUploadPost);
} else {
  initUploadPost();
}
