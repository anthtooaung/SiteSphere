const injectSiteLogoStyles = () => {
  if (document.querySelector("style[data-sitesphere-logo]")) return;

  const style = document.createElement("style");
  style.dataset.sitesphereLogo = "true";
  style.textContent = `
    site-logo {
      display: contents;
    }

    .brand-title {
      position: absolute;
      left: 34px;
      top: 28px;
      z-index: 5;
      display: inline-flex;
      align-items: center;
      gap: 12px;
      color: var(--black, #050816);
      font-size: 1.26rem;
      font-weight: 900;
      letter-spacing: 0;
      text-decoration: none;
    }

    .brand-mark {
      position: relative;
      display: inline-grid;
      width: 40px;
      height: 40px;
      place-items: center;
      border: 1px solid rgba(53, 109, 243, 0.2);
      border-radius: 13px;
      background: linear-gradient(145deg, var(--blue, #356df3), var(--blue-dark, #2154d8));
      color: var(--white, #ffffff);
      font-size: 1rem;
      font-weight: 900;
      box-shadow: 0 14px 28px rgba(53, 109, 243, 0.24);
      overflow: hidden;
    }

    .brand-mark::before {
      position: absolute;
      inset: 8px 4px;
      border: 1px solid rgba(255, 255, 255, 0.7);
      border-radius: 999px;
      content: "";
      transform: rotate(-28deg);
    }

    .brand-mark::after {
      position: absolute;
      right: 8px;
      top: 8px;
      width: 6px;
      height: 6px;
      border-radius: 999px;
      background: var(--white, #ffffff);
      content: "";
      box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.18);
    }

    .brand-word {
      display: inline-flex;
      align-items: baseline;
      gap: 0;
    }

    .brand-site {
      color: var(--black, #050816);
    }

    .brand-sphere {
      color: var(--blue, #356df3);
    }

    .auth-shell.is-register .brand-site,
    .auth-shell.is-register .brand-sphere {
      color: var(--white, #ffffff);
    }

    .auth-shell.is-register .brand-mark {
      border-color: rgba(255, 255, 255, 0.5);
      background: var(--white, #ffffff);
      color: var(--blue, #356df3);
      box-shadow: 0 14px 32px rgba(5, 8, 22, 0.18);
    }

    .auth-shell.is-register .brand-mark::before {
      border-color: rgba(53, 109, 243, 0.52);
    }

    .auth-shell.is-register .brand-mark::after {
      background: var(--blue, #356df3);
      box-shadow: 0 0 0 3px rgba(53, 109, 243, 0.14);
    }

    @media (max-width: 900px) {
      .brand-title {
        position: relative;
        left: auto;
        top: auto;
        z-index: 10;
        width: 100%;
        padding: 16px 18px 10px;
        font-size: 1.05rem;
      }

      .brand-mark {
        width: 36px;
        height: 36px;
        border-radius: 12px;
      }

      .auth-shell.is-register .brand-site {
        color: var(--black, #050816);
      }

      .auth-shell.is-register .brand-sphere {
        color: var(--blue, #356df3);
      }

      .auth-shell.is-register .brand-mark {
        border-color: rgba(53, 109, 243, 0.24);
        background: linear-gradient(145deg, var(--blue, #356df3), var(--blue-dark, #2154d8));
        color: var(--white, #ffffff);
      }
    }

    @media (max-width: 430px) {
      .brand-title {
        padding: 14px 16px 8px;
        font-size: 1rem;
      }
    }
  `;

  document.head.appendChild(style);
};

class SiteLogo extends HTMLElement {
  connectedCallback() {
    injectSiteLogoStyles();

    const escapeAttribute = (value) =>
      String(value).replace(/&/g, "&amp;").replace(/"/g, "&quot;");
    const href = escapeAttribute(this.getAttribute("href") || "#");
    const label = escapeAttribute(
      this.getAttribute("aria-label") || "SiteSphere home",
    );

    this.innerHTML = `
      <a href="${href}" class="brand-title" aria-label="${label}">
        <span class="brand-mark" aria-hidden="true">S</span>
        <span class="brand-word">
          <span class="brand-site">Site</span><span class="brand-sphere">Sphere</span>
        </span>
      </a>
    `;
  }
}

if (!customElements.get("site-logo")) {
  customElements.define("site-logo", SiteLogo);
}
