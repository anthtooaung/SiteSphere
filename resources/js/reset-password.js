const initResetPasswordOtp = () => {
  const form = document.getElementById("resetOtpForm");
  const hiddenInput = document.getElementById("reset-otp-code");
  const digits = Array.from(document.querySelectorAll(".otp-digit"));

  if (!form || !hiddenInput || digits.length === 0) {
    return;
  }

  const syncInput = () => {
    hiddenInput.value = digits.map((input) => input.value).join("");
  };

  const fillFromValue = (value) => {
    const numbers = value.replace(/\D/g, "").slice(0, digits.length);

    digits.forEach((input, index) => {
      input.value = numbers[index] || "";
    });

    syncInput();
  };

  fillFromValue(hiddenInput.value);

  digits.forEach((input, index) => {
    input.addEventListener("input", () => {
      input.value = input.value.replace(/\D/g, "").slice(-1);
      syncInput();

      if (input.value && digits[index + 1]) {
        digits[index + 1].focus();
      }
    });

    input.addEventListener("keydown", (event) => {
      if (event.key === "Backspace" && !input.value && digits[index - 1]) {
        digits[index - 1].focus();
      }

      if (event.key === "ArrowLeft" && digits[index - 1]) {
        event.preventDefault();
        digits[index - 1].focus();
      }

      if (event.key === "ArrowRight" && digits[index + 1]) {
        event.preventDefault();
        digits[index + 1].focus();
      }
    });

    input.addEventListener("paste", (event) => {
      const pastedDigits = event.clipboardData
        .getData("text")
        .replace(/\D/g, "")
        .slice(0, digits.length - index);

      if (!pastedDigits) {
        return;
      }

      event.preventDefault();

      pastedDigits.split("").forEach((digit, offset) => {
        const target = digits[index + offset];

        if (target) {
          target.value = digit;
        }
      });

      syncInput();

      const nextEmpty = digits.find((digitInput) => !digitInput.value);
      (nextEmpty || digits[digits.length - 1]).focus();
    });
  });

  form.addEventListener("submit", syncInput);
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initResetPasswordOtp);
} else {
  initResetPasswordOtp();
}
