document.addEventListener("DOMContentLoaded", () => {
  const STORAGE_KEY = "cookie-consenter-consent";
  const SCHEMA_VERSION = 1;
  const config = window.CookieConsenterConfig || {};
  const POLICY_VERSION = String(config.policyVersion || "1");
  const CONSENT_DURATION_DAYS = Math.max(
    1,
    Number(config.consentDurationDays) || 180,
  );

  const acceptButton = document.getElementById("cookie-consenter-accept");
  const declineButton = document.getElementById("cookie-consenter-decline");
  const banner = document.getElementById("cookie-consenter-banner");
  const settingsButton = document.getElementById("cookie-consenter-settings");
  const closeButton = document.getElementById("cookie-consenter-close");

  if (
    !acceptButton ||
    !declineButton ||
    !closeButton ||
    !banner ||
    !settingsButton
  ) {
    return;
  }

  const hideBanner = () => {
    banner.hidden = true;
    settingsButton.hidden = false;
    closeButton.hidden = true;
    settingsButton.setAttribute("aria-expanded", "false");
  };

  const showBanner = (isReopening = false) => {
    banner.hidden = false;
    settingsButton.hidden = true;
    closeButton.hidden = !isReopening;
    settingsButton.setAttribute("aria-expanded", "true");

    if (isReopening) {
      acceptButton.focus();
    }
  };

  const getConsent = () => {
    try {
      const storedConsent = localStorage.getItem(STORAGE_KEY);

      if (!storedConsent) {
        return null;
      }

      const consent = JSON.parse(storedConsent);

      if (!isValidConsent(consent)) {
        clearConsent();
        return null;
      }

      return consent;
    } catch (error) {
      clearConsent();
      return null;
    }
  };

  const isValidDate = (value) => {
    return Number.isFinite(Date.parse(value));
  };

  const isValidConsent = (consent) => {
    return Boolean(
      consent &&
      consent.schemaVersion === SCHEMA_VERSION &&
      consent.policyVersion === POLICY_VERSION &&
      consent.choices &&
      consent.choices.necessary === true &&
      typeof consent.choices.optional === "boolean" &&
      isValidDate(consent.updatedAt) &&
      isValidDate(consent.expiresAt) &&
      Date.parse(consent.expiresAt) > Date.now(),
    );
  };

  const clearConsent = () => {
    try {
      localStorage.removeItem(STORAGE_KEY);
    } catch (error) {}
  };

  const createConsent = (optional) => {
    const updatedAt = new Date();
    const expiresAt = new Date(
      updatedAt.getTime() + CONSENT_DURATION_DAYS * 24 * 60 * 60 * 1000,
    );

    return {
      schemaVersion: SCHEMA_VERSION,
      policyVersion: POLICY_VERSION,
      choices: {
        necessary: true,
        optional,
      },
      updatedAt: updatedAt.toISOString(),
      expiresAt: expiresAt.toISOString(),
    };
  };

  const saveConsent = (optional) => {
    const consent = createConsent(optional);

    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(consent));
    } catch (error) {}
  };

  if (getConsent()) {
    hideBanner();
  } else {
    showBanner();
  }

  banner.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && !closeButton.hidden) {
      hideBanner();
      settingsButton.focus();
    }
  });

  closeButton.addEventListener("click", () => {
    hideBanner();
    settingsButton.focus();
  });

  settingsButton.addEventListener("click", () => {
    showBanner(true);
  });

  acceptButton.addEventListener("click", () => {
    saveConsent(true);
    hideBanner();
    settingsButton.focus();
  });

  declineButton.addEventListener("click", () => {
    saveConsent(false);
    hideBanner();
    settingsButton.focus();
  });
});
