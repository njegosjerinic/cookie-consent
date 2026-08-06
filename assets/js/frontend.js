document.addEventListener("DOMContentLoaded", () => {
  //Osnovni elementi

  const STORAGE_KEY = "cookie-consenter-consent";
  const SCHEMA_VERSION = 2;
  const config = window.CookieConsenterConfig || {};
  const POLICY_VERSION = String(config.policyVersion || "1");
  const CONSENT_DURATION_DAYS = Math.max(
    1,
    Number(config.consentDurationDays) || 180,
  );
  const OPTIONAL_CATEGORIES = ["preferences", "analytics", "marketing"];

  //Elementi DOM-a
  const acceptButton = document.getElementById("cookie-consenter-accept");
  const declineButton = document.getElementById("cookie-consenter-decline");
  const banner = document.getElementById("cookie-consenter-banner");
  const settingsButton = document.getElementById("cookie-consenter-settings");
  const closeButton = document.getElementById("cookie-consenter-close");
  const manageButton = document.getElementById("cookie-consenter-manage");
  const preferencesPanel = document.getElementById(
    "cookie-consenter-preferences",
  );
  const savePreferencesButton = document.getElementById(
    "cookie-consenter-save-preferences",
  );
  const preferencesInput = document.getElementById(
    "cookie-consenter-category-preferences",
  );
  const analyticsInput = document.getElementById(
    "cookie-consenter-category-analytics",
  );
  const marketingInput = document.getElementById(
    "cookie-consenter-category-marketing",
  );

  //Test postojanja svih elemenata DOM-a
  if (
    !acceptButton ||
    !declineButton ||
    !closeButton ||
    !banner ||
    !settingsButton ||
    !manageButton ||
    !preferencesPanel ||
    !savePreferencesButton ||
    !preferencesInput ||
    !analyticsInput ||
    !marketingInput
  ) {
    return;
  }

  let currentConsent = null;

  const hideBanner = () => {
    banner.hidden = true;
    settingsButton.hidden = false;
    closeButton.hidden = true;
    settingsButton.setAttribute("aria-expanded", "false");
    setPreferenceVisibility(false);
  };

  const showBanner = (isReopening = false) => {
    setPreferenceVisibility(false);
    banner.hidden = false;
    settingsButton.hidden = true;
    closeButton.hidden = !isReopening;
    settingsButton.setAttribute("aria-expanded", "true");

    if (isReopening) {
      acceptButton.focus();
    }
  };

  const populatePreferences = (consent) => {
    const choices = consent?.choices;

    preferencesInput.checked = choices?.preferences ?? false;
    analyticsInput.checked = choices?.analytics ?? false;
    marketingInput.checked = choices?.marketing ?? false;
  };

  const setPreferenceVisibility = (isVisible) => {
    preferencesPanel.hidden = !isVisible;
    manageButton.setAttribute("aria-expanded", isVisible ? "true" : "false");

    if (isVisible) {
      preferencesInput.focus();
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
      OPTIONAL_CATEGORIES.every(
        (category) => typeof consent.choices[category] === "boolean",
      ) &&
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

  const createConsent = (choices) => {
    const updatedAt = new Date();
    const expiresAt = new Date(
      updatedAt.getTime() + CONSENT_DURATION_DAYS * 24 * 60 * 60 * 1000,
    );

    return {
      schemaVersion: SCHEMA_VERSION,
      policyVersion: POLICY_VERSION,
      choices: {
        necessary: true,
        preferences: Boolean(choices.preferences),
        analytics: Boolean(choices.analytics),
        marketing: Boolean(choices.marketing),
      },
      updatedAt: updatedAt.toISOString(),
      expiresAt: expiresAt.toISOString(),
    };
  };

  const saveConsent = (choices) => {
    const consent = createConsent(choices);

    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(consent));
    } catch (error) {}

    return consent;
  };

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
    currentConsent = saveConsent({
      preferences: true,
      analytics: true,
      marketing: true,
    });
    hideBanner();
    settingsButton.focus();
  });

  declineButton.addEventListener("click", () => {
    currentConsent = saveConsent({
      preferences: false,
      analytics: false,
      marketing: false,
    });
    hideBanner();
    settingsButton.focus();
  });

  manageButton.addEventListener("click", () => {
    const isOpen = !preferencesPanel.hidden;

    if (!isOpen) {
      populatePreferences(currentConsent);
    }

    setPreferenceVisibility(!isOpen);
  });

  savePreferencesButton.addEventListener("click", () => {
    currentConsent = saveConsent({
      preferences: preferencesInput.checked,
      analytics: analyticsInput.checked,
      marketing: marketingInput.checked,
    });

    hideBanner();
    settingsButton.focus();
  });

  currentConsent = getConsent();

  if (currentConsent) {
    populatePreferences(currentConsent);
    hideBanner();
  } else {
    populatePreferences(null);
    showBanner();
  }

  document.addEventListener("cookie-consenter:change", (event) => {
    console.log(event.detail.consent);
  });
});
