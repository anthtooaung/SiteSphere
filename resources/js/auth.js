/*
  Section: login-panel
  Path: /login
  Required fields: user_email, password
*/

document.addEventListener("DOMContentLoaded", () => {
  const authShell = document.getElementById("authShell");
  const loginForm = document.getElementById("loginForm");
  const registerForm = document.getElementById("registerForm");
  const registerPanel = document.querySelector(".register-panel");
  const showRegister = document.getElementById("showRegister");
  const showLogin = document.getElementById("showLogin");
  const forgotPasswordBtn = document.getElementById("forgotPasswordBtn");
  const loginPassword = document.getElementById("login-password");
  const toggleLoginPassword = document.getElementById("toggleLoginPassword");
  const socialButtons = document.querySelectorAll(".social-button");

  const registrationModal = document.getElementById("registrationModal");
  const closeRegistrationFlow = document.getElementById(
    "closeRegistrationFlow",
  );
  const flowSteps = document.querySelectorAll(".flow-step");
  const otpForm = document.getElementById("otpForm");
  const otpInput = document.getElementById("otp-code");
  const otpDigits = Array.from(document.querySelectorAll(".otp-digit"));
  const otpEmailTarget = document.getElementById("otpEmailTarget");
  const otpTimer = document.getElementById("otpTimer");
  const demoOtpCode = document.getElementById("demoOtpCode");
  const resendOtpBtn = document.getElementById("resendOtpBtn");
  const profileForm = document.getElementById("profileForm");
  const skipProfileBtn = document.getElementById("skipProfileBtn");
  const profileActions = profileForm.querySelector(".flow-actions");
  const profileDob = document.getElementById("profile-dob");
  const profilePhone = document.getElementById("profile-phone");
  const profileBio = document.getElementById("profile-bio");
  const profileImage = document.getElementById("profile-image");
  const profileImagePreview = document.getElementById("profileImagePreview");
  const profileImageThumb = document.getElementById("profileImageThumb");
  const confirmPreview = document.getElementById("confirmPreview");
  const confirmAvatar = document.getElementById("confirmAvatar");
  const confirmUsername = document.getElementById("confirmUsername");
  const confirmLayoutText = document.getElementById("confirmLayoutText");
  const backToProfileBtn = document.getElementById("backToProfileBtn");
  const confirmRegisterBtn = document.getElementById("confirmRegisterBtn");

  const OTP_DURATION_MS = 5 * 60 * 1000;
  const DESKTOP_SLIDE_ANIMATION_MS = 640;
  const MOBILE_HANDOFF_ANIMATION_MS = 820;
  const MOBILE_HANDOFF_SWITCH_MS = 260;
  const STEP_ANIMATION_MS = 460;
  const OTP_FEEDBACK_MS = 420;
  const mobileHandoffQuery = window.matchMedia("(max-width: 900px)");

  const ensureProfileContinueButton = () => {
    let continueProfileBtn = document.getElementById("continueProfileBtn");

    if (continueProfileBtn || !profileActions) {
      return continueProfileBtn;
    }

    continueProfileBtn = document.createElement("button");
    continueProfileBtn.type = "submit";
    continueProfileBtn.className = "primary-button";
    continueProfileBtn.id = "continueProfileBtn";
    continueProfileBtn.textContent = "Continue";
    profileActions.appendChild(continueProfileBtn);

    return continueProfileBtn;
  };

  ensureProfileContinueButton();

  let slideTimer = null;
  let modeSwitchTimer = null;
  let stepAnimationTimer = null;
  let otpFeedbackTimer = null;
  let otpAdvanceTimer = null;

  let otpState = {
    code: "",
    expiresAt: 0,
    timerId: null,
  };

  let registrationData = {
    account: null,
    profile: {},
    profileImageDataUrl: "",
  };

  /*
    Keep the registration flow inside the register panel.
    This prevents the modal from covering the whole screen.
  */
  if (registerPanel && registrationModal) {
    registerPanel.appendChild(registrationModal);
    registrationModal.removeAttribute("aria-modal");
    registrationModal.setAttribute("role", "region");
  }

  const showAlert = (options) => {
    if (typeof Swal !== "undefined") {
      return Swal.fire({
        background: "#ffffff",
        color: "#050816",
        confirmButtonColor: "#356df3",
        customClass: {
          popup: "sitesphere-alert",
        },
        ...options,
      });
    }

    alert(options.text || options.title || "Notification");
    return Promise.resolve();
  };

  const setModeControlsDisabled = (isDisabled) => {
    [showRegister, showLogin].forEach((button) => {
      if (!button) return;

      button.disabled = isDisabled;
      button.setAttribute("aria-disabled", String(isDisabled));
    });
  };

  const clearOtpTimer = () => {
    if (otpState.timerId) {
      clearInterval(otpState.timerId);
      otpState.timerId = null;
    }
  };

  const clearOtpFeedback = () => {
    if (otpFeedbackTimer) {
      clearTimeout(otpFeedbackTimer);
      otpFeedbackTimer = null;
    }

    otpForm.classList.remove("is-otp-error", "is-otp-success");
  };

  const clearOtpAdvance = () => {
    if (otpAdvanceTimer) {
      clearTimeout(otpAdvanceTimer);
      otpAdvanceTimer = null;
    }
  };

  const playOtpFeedback = (className) => {
    clearOtpFeedback();
    void otpForm.offsetWidth;

    otpForm.classList.add(className);

    otpFeedbackTimer = setTimeout(() => {
      otpForm.classList.remove(className);
      otpFeedbackTimer = null;
    }, OTP_FEEDBACK_MS);
  };

  const applyModeState = (mode) => {
    const isRegister = mode === "register";

    authShell.dataset.mode = mode;
    authShell.classList.toggle("is-register", isRegister);

    delete authShell.dataset.flowStep;
    delete authShell.dataset.flowActive;

    if (!isRegister) {
      registrationModal.classList.add("is-hidden");
      registerPanel.classList.remove("is-flow-active");
      clearOtpTimer();
    }
  };

  const playSliderAnimation = (mode) => {
    if (slideTimer) {
      clearTimeout(slideTimer);
      slideTimer = null;
    }

    if (modeSwitchTimer) {
      clearTimeout(modeSwitchTimer);
      modeSwitchTimer = null;
    }

    authShell.classList.remove(
      "is-sliding",
      "is-mobile-handoff",
      "is-mobile-to-register",
      "is-mobile-to-login",
    );

    void authShell.offsetWidth;

    const isMobileHandoff = mobileHandoffQuery.matches;
    setModeControlsDisabled(true);

    if (!isMobileHandoff) {
      authShell.classList.add("is-sliding");
      applyModeState(mode);

      slideTimer = setTimeout(() => {
        authShell.classList.remove("is-sliding");
        setModeControlsDisabled(false);
        slideTimer = null;
      }, DESKTOP_SLIDE_ANIMATION_MS);
      return;
    }

    authShell.classList.add(
      "is-sliding",
      "is-mobile-handoff",
      mode === "register" ? "is-mobile-to-register" : "is-mobile-to-login",
    );

    modeSwitchTimer = setTimeout(() => {
      applyModeState(mode);
      modeSwitchTimer = null;
    }, MOBILE_HANDOFF_SWITCH_MS);

    slideTimer = setTimeout(() => {
      authShell.classList.remove(
        "is-sliding",
        "is-mobile-handoff",
        "is-mobile-to-register",
        "is-mobile-to-login",
      );
      setModeControlsDisabled(false);
      slideTimer = null;
    }, MOBILE_HANDOFF_ANIMATION_MS);
  };

  const setMode = (mode, updateUrl = true) => {
    const previousMode = authShell.dataset.mode || "login";
    const hasMode = Boolean(authShell.dataset.mode);

    if (!hasMode || previousMode !== mode) {
      if (updateUrl) {
        playSliderAnimation(mode);
      } else {
        applyModeState(mode);
      }
    }

    if (updateUrl) {
      history.pushState(
        null,
        "",
        mode === "register" ? "/register" : "/login",
      );
    }
  };

  const escapeHtml = (value) =>
    String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");

  const valueOrDash = (value) => {
    const text = String(value || "").trim();
    return text || "-";
  };

  const formatTime = (milliseconds) => {
    const totalSeconds = Math.max(0, Math.ceil(milliseconds / 1000));
    const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, "0");
    const seconds = String(totalSeconds % 60).padStart(2, "0");

    return `${minutes}:${seconds}`;
  };

  // Frontend demo only: Laravel should generate, store, email, and expire OTP codes.
  const generateOtpCode = () =>
    String(Math.floor(100000 + Math.random() * 900000));

  const updateOtpTimer = () => {
    const remaining = otpState.expiresAt - Date.now();

    otpTimer.textContent = formatTime(remaining);

    if (remaining <= 0) {
      clearOtpTimer();
      otpTimer.textContent = "Expired";
    }
  };

  const syncOtpInput = () => {
    otpInput.value = otpDigits.map((input) => input.value).join("");
  };

  const clearOtpInputs = () => {
    otpDigits.forEach((input) => {
      input.value = "";
    });
    syncOtpInput();
  };

  const sendOtp = () => {
    otpState.code = generateOtpCode();
    otpState.expiresAt = Date.now() + OTP_DURATION_MS;

    demoOtpCode.textContent = otpState.code;
    clearOtpFeedback();
    clearOtpAdvance();
    clearOtpInputs();

    clearOtpTimer();
    updateOtpTimer();

    otpState.timerId = setInterval(updateOtpTimer, 1000);
  };

  const setFlowStep = (step) => {
    authShell.dataset.flowStep = step;

    if (stepAnimationTimer) {
      clearTimeout(stepAnimationTimer);
      stepAnimationTimer = null;
    }

    let activeStep = null;

    flowSteps.forEach((flowStep) => {
      const isActive = flowStep.dataset.step === step;

      flowStep.classList.toggle("is-hidden", !isActive);
      flowStep.classList.remove("is-step-entering");

      if (isActive) {
        activeStep = flowStep;
      }
    });

    if (!activeStep) return;

    void activeStep.offsetWidth;
    activeStep.classList.add("is-step-entering");

    stepAnimationTimer = setTimeout(() => {
      activeStep.classList.remove("is-step-entering");
      stepAnimationTimer = null;
    }, STEP_ANIMATION_MS);
  };

  const openRegistrationFlow = (account) => {
    registrationData = {
      account,
      profile: {},
      profileImageDataUrl: "",
    };

    otpEmailTarget.textContent = account.email;
    registrationModal.classList.remove("is-hidden");
    registerPanel.classList.add("is-flow-active");
    authShell.dataset.flowActive = "true";

    setFlowStep("otp");
    sendOtp();
    otpDigits[0]?.focus();
  };

  const resetRegistrationFlow = () => {
    clearOtpTimer();

    otpState = {
      code: "",
      expiresAt: 0,
      timerId: null,
    };

    registrationData = {
      account: null,
      profile: {},
      profileImageDataUrl: "",
    };

    otpForm.reset();
    clearOtpFeedback();
    clearOtpAdvance();
    clearOtpInputs();
    profileForm.reset();

    profileImageThumb.removeAttribute("src");
    profileImagePreview.classList.add("is-hidden");
  };

  const hideRegistrationFlow = () => {
    registrationModal.classList.add("is-hidden");
    registerPanel.classList.remove("is-flow-active");

    delete authShell.dataset.flowActive;
    delete authShell.dataset.flowStep;

    resetRegistrationFlow();
  };

  const collectProfile = () => ({
    user_dob: profileDob.value,
    user_phone: profilePhone.value,
    user_bio: profileBio.value.trim(),
  });

  const renderConfirmation = () => {
    const profile = registrationData.profile || {};
    const account = registrationData.account;

    if (!account) return;

    const initial = account.username.charAt(0).toUpperCase();

    confirmUsername.textContent = account.username;
    confirmLayoutText.textContent = "SiteSphere member profile";

    if (registrationData.profileImageDataUrl) {
      confirmAvatar.classList.add("has-image");
      confirmAvatar.innerHTML = `<img src="${registrationData.profileImageDataUrl}" alt="Profile image preview" />`;
    } else {
      confirmAvatar.classList.remove("has-image");
      confirmAvatar.textContent = initial;
    }

    const rows = [
      ["Username", account.username],
      ["Email address", account.email],
      ["Date of birth", profile.user_dob],
      ["Phone number", profile.user_phone],
      ["Bio", profile.user_bio],
      [
        "Profile image",
        registrationData.profileImageDataUrl ? "Selected" : "Not added",
      ],
    ];

    confirmPreview.innerHTML = rows
      .map(
        ([label, value]) =>
          `<div class="preview-row">
            <span>${escapeHtml(label)}</span>
            <strong>${escapeHtml(valueOrDash(value))}</strong>
          </div>`,
      )
      .join("");
  };

  showRegister.addEventListener("click", () => {
    setMode("register");
  });

  showLogin.addEventListener("click", () => {
    setMode("login");
  });

  toggleLoginPassword.addEventListener("click", () => {
    const isVisible = loginPassword.type === "text";

    loginPassword.type = isVisible ? "password" : "text";
    loginPassword.classList.toggle("is-visible-password", !isVisible);

    toggleLoginPassword.classList.toggle("is-visible", !isVisible);
    toggleLoginPassword.setAttribute(
      "aria-label",
      isVisible ? "Show password" : "Hide password",
    );
    toggleLoginPassword.setAttribute("aria-pressed", String(!isVisible));
  });

  socialButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const provider = button.dataset.provider;

      showAlert({
        title: `${provider} selected`,
        text: "This is a front-end action. Backend OAuth can be connected later.",
        icon: "info",
      });
    });
  });

  forgotPasswordBtn.addEventListener("click", () => {
    const email = document.getElementById("login-email").value.trim();

    showAlert({
      title: "Password reset",
      text: email
        ? `A reset link would be sent to ${email} when the backend is connected.`
        : "Enter your email first, then this front-end action can show the reset flow.",
      icon: "info",
    });
  });

  // Frontend demo only: replace with Laravel login POST and server-side validation.
  loginForm.addEventListener("submit", (event) => {
    event.preventDefault();

    const email = document.getElementById("login-email").value.trim();

    showAlert({
      title: "Login successful",
      text: `${email || "Your account"} is ready for SiteSphere.`,
      icon: "success",
    });

    loginForm.reset();
  });

  // Frontend demo only: Laravel must revalidate these fields before creating the user.
  registerForm.addEventListener("submit", (event) => {
    event.preventDefault();

    const username = document.getElementById("reg-name").value.trim();
    const email = document.getElementById("reg-email").value.trim();
    const password = document.getElementById("reg-password").value;
    const confirmPassword = document.getElementById("reg-confirm").value;
    const specialCharacterCount = (password.match(/[^A-Za-z0-9]/g) || [])
      .length;

    if (!username || !email || !password || !confirmPassword) {
      showAlert({
        title: "Missing information",
        text: "Please complete all required registration fields.",
        icon: "error",
      });
      return;
    }

    if (specialCharacterCount < 3) {
      showAlert({
        title: "Add more special characters",
        text: "Your password needs at least 3 symbols like !, @, #, or $.",
        icon: "error",
      });
      return;
    }

    if (password !== confirmPassword) {
      showAlert({
        title: "Passwords do not match",
        text: "Please confirm your password and try again.",
        icon: "error",
      });
      return;
    }

    openRegistrationFlow({ username, email });
  });

  closeRegistrationFlow.addEventListener("click", hideRegistrationFlow);

  resendOtpBtn.addEventListener("click", () => {
    sendOtp();
    otpDigits[0]?.focus();
  });

  otpDigits.forEach((input, index) => {
    input.addEventListener("input", () => {
      const digit = input.value.replace(/\D/g, "").slice(-1);
      input.value = digit;
      clearOtpFeedback();
      syncOtpInput();

      if (digit && otpDigits[index + 1]) {
        otpDigits[index + 1].focus();
      }
    });

    input.addEventListener("keydown", (event) => {
      if (event.key === "Backspace" && !input.value && otpDigits[index - 1]) {
        otpDigits[index - 1].focus();
      }

      if (event.key === "ArrowLeft" && otpDigits[index - 1]) {
        event.preventDefault();
        otpDigits[index - 1].focus();
      }

      if (event.key === "ArrowRight" && otpDigits[index + 1]) {
        event.preventDefault();
        otpDigits[index + 1].focus();
      }
    });

    input.addEventListener("paste", (event) => {
      const pastedDigits = event.clipboardData
        .getData("text")
        .replace(/\D/g, "")
        .slice(0, otpDigits.length - index);

      if (!pastedDigits) return;

      event.preventDefault();

      pastedDigits.split("").forEach((digit, offset) => {
        const target = otpDigits[index + offset];
        if (target) target.value = digit;
      });

      syncOtpInput();
      clearOtpFeedback();

      const nextEmpty = otpDigits.find((digitInput) => !digitInput.value);
      (nextEmpty || otpDigits[otpDigits.length - 1]).focus();
    });
  });

  // Frontend demo only: replace this comparison with Laravel OTP verification.
  otpForm.addEventListener("submit", (event) => {
    event.preventDefault();
    syncOtpInput();

    if (Date.now() > otpState.expiresAt) {
      playOtpFeedback("is-otp-error");
      showAlert({
        title: "OTP expired",
        text: "Please resend the OTP and use the newest code.",
        icon: "error",
      });
      return;
    }

    if (otpInput.value.length !== otpDigits.length) {
      playOtpFeedback("is-otp-error");
      showAlert({
        title: "Complete the OTP",
        text: "Please enter all 6 digits from the newest OTP code.",
        icon: "error",
      });
      return;
    }

    if (otpInput.value.trim() !== otpState.code) {
      playOtpFeedback("is-otp-error");
      showAlert({
        title: "Invalid OTP",
        text: "Use the newest OTP code. Old OTP codes stop working after resend.",
        icon: "error",
      });
      return;
    }

    playOtpFeedback("is-otp-success");
    clearOtpTimer();
    clearOtpAdvance();

    otpAdvanceTimer = setTimeout(() => {
      otpAdvanceTimer = null;
      if (authShell.dataset.flowActive !== "true") return;

      setFlowStep("profile");
    }, OTP_FEEDBACK_MS);
  });

  profileImage.addEventListener("change", () => {
    const file = profileImage.files[0];

    if (!file) {
      registrationData.profileImageDataUrl = "";
      profileImageThumb.removeAttribute("src");
      profileImagePreview.classList.add("is-hidden");
      return;
    }

    const reader = new FileReader();

    reader.addEventListener("load", () => {
      registrationData.profileImageDataUrl = reader.result;
      profileImageThumb.src = reader.result;
      profileImagePreview.classList.remove("is-hidden");
    });

    reader.readAsDataURL(file);
  });

  const continueToConfirmation = () => {
    registrationData.profile = collectProfile();
    renderConfirmation();
    setFlowStep("confirm");
  };

  profileForm.addEventListener("submit", (event) => {
    event.preventDefault();
    continueToConfirmation();
  });

  skipProfileBtn.addEventListener("click", () => {
    continueToConfirmation();
  });

  backToProfileBtn.addEventListener("click", () => {
    setFlowStep("profile");
  });

  // Frontend demo only: Laravel should finalize the account and persist profile data here.
  confirmRegisterBtn.addEventListener("click", () => {
    showAlert({
      title: "Account ready",
      text: "Registration confirmed. Redirecting to login.",
      icon: "success",
    }).then(() => {
      hideRegistrationFlow();
      registerForm.reset();
      setMode("login");
    });
  });

  const initialMode = window.location.pathname.includes("register") ? "register" : "login";
  setMode(initialMode, false);

  window.addEventListener("popstate", () => {
    const mode = window.location.pathname.includes("register") ? "register" : "login";
    setMode(mode, false);
  });
});
