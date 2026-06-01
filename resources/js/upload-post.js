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

  const selectedTagRecords = () => Object.values(categories)
    .flatMap((category) => category.tags || [])
    .filter((tag) => selectedTags.includes(String(tag.id)));

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
      button.classList.toggle("is-active", button.dataset.categoryButton === currentCategory);
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
        button.classList.toggle("is-selected", selectedTags.includes(String(tag.id)));
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
    const category = categories[currentCategory]?.name || selectedTagRecords()[0]?.name || "Selected category";
    const titleElement = previewCard.querySelector("[data-post-card-title]");
    const urlElement = previewCard.querySelector("[data-post-card-url]");
    const linkElement = previewCard.querySelector("[data-post-card-link]");
    const categoryElement = previewCard.querySelector("[data-post-card-category]");
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

    if (categoryElement) {
      const dot = categoryElement.querySelector("span");
      categoryElement.textContent = "";

      if (dot) {
        categoryElement.appendChild(dot);
      }

      categoryElement.append(category);
    }

    if (descriptionElement) {
      descriptionElement.textContent = description;
    }
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

  discardButton?.addEventListener("click", () => {
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
