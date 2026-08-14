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
  const COOKIE_PREFIXES = {
    analytics: ["_ga", "_gid", "_gat"],
    marketing: ["_gcl_"],
  };
  const BLOCKED_SCRIPT_SELECTOR =
    'script[type="text/plain"][data-cookie-consenter-script][data-cookie-consenter-category]';

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
    manageButton.hidden = isVisible;
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

  const requiresReloadAfterWithdrawal = (previousConsent, nextConsent) => {
    if (!previousConsent) {
      return false;
    }

    return OPTIONAL_CATEGORIES.some((category) => {
      return (
        previousConsent.choices[category] === true &&
        nextConsent.choices[category] === false
      );
    });
  };

  const deleteCookie = (name) => {
    const hostname = window.location.hostname || "";
    const domainParts = hostname.split(".").filter(Boolean);
    const domains = [""];

    for (let index = 0; index < domainParts.length - 1; index += 1) {
      domains.push(domainParts.slice(index).join("."));
    }

    domains.forEach((domain) => {
      document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/${domain ? `; domain=${domain}` : ""}; SameSite=Lax`;
    });
  };

  const removeDeniedCookies = (consent) => {
    if (!consent?.choices || typeof document.cookie !== "string") {
      return;
    }

    const cookieNames = document.cookie
      .split(";")
      .map((cookie) => cookie.trim().split("=")[0])
      .filter(Boolean);

    Object.entries(COOKIE_PREFIXES).forEach(([category, prefixes]) => {
      if (consent.choices[category] === true) {
        return;
      }

      cookieNames
        .filter((name) => prefixes.some((prefix) => name.startsWith(prefix)))
        .forEach(deleteCookie);
    });
  };

  const copyConsent = (consent) => {
    if (!consent) {
      return null;
    }

    return {
      ...consent,
      choices: {
        ...consent.choices,
      },
    };
  };

  const hasConsent = (category) => {
    if (category === "necessary") {
      return true;
    }

    if (!OPTIONAL_CATEGORIES.includes(category)) {
      return false;
    }

    return currentConsent?.choices?.[category] === true;
  };

  const getBlockedScripts = () => {
    return Array.from(document.querySelectorAll(BLOCKED_SCRIPT_SELECTOR));
  };

  const getScriptCategory = (script) => {
    const category = script.dataset.cookieConsenterCategory;

    if (!OPTIONAL_CATEGORIES.includes(category)) {
      return null;
    }

    return category;
  };

  const activateScript = (placeholder) => {
    const category = getScriptCategory(placeholder);

    if (!category || !hasConsent(category)) {
      return false;
    }

    const executableScript = document.createElement("script");
    const originalType = placeholder.dataset.cookieConsenterType;

    Array.from(placeholder.attributes).forEach((attribute) => {
      if (
        attribute.name === "type" ||
        attribute.name === "data-src" ||
        attribute.name.startsWith("data-cookie-consenter-")
      ) {
        return;
      }

      executableScript.setAttribute(attribute.name, attribute.value);
    });

    if (originalType) {
      executableScript.type = originalType;
    }

    if (placeholder.dataset.src) {
      executableScript.src = placeholder.dataset.src;

      if (!executableScript.async && !executableScript.defer) {
        executableScript.async = false;
      }
    } else {
      executableScript.textContent = placeholder.textContent;
    }

    placeholder.dataset.cookieConsenterState = "activated";
    placeholder.replaceWith(executableScript);

    return true;
  };

  const synchronizeConsentScripts = () => {
    getBlockedScripts().forEach((placeholder) => {
      if (placeholder.dataset.cookieConsenterState === "activated") {
        return;
      }

      activateScript(placeholder);
    });
  };

  const exposeConsentApi = () => {
    window.CookieConsenter = Object.freeze({
      getConsent: () => copyConsent(currentConsent),

      hasConsent: (category) => hasConsent(category),

      openSettings: () => {
        showBanner(true);
      },

      refresh: () => {
        synchronizeConsentScripts();
      },
    });
  };

  const dispatchConsentEvent = (eventName, source) => {
    document.dispatchEvent(
      new CustomEvent(eventName, {
        detail: {
          consent: copyConsent(currentConsent),
          source,
        },
      }),
    );
  };

  const updateConsent = (choices, source) => {
    const previousConsent = currentConsent;
    const nextConsent = saveConsent(choices);

    currentConsent = nextConsent;
    removeDeniedCookies(currentConsent);
    dispatchConsentEvent("cookie-consenter:change", source);

    if (requiresReloadAfterWithdrawal(previousConsent, nextConsent)) {
      window.location.reload();
    }
  };

  document.addEventListener(
    "cookie-consenter:change",
    synchronizeConsentScripts,
  );

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
    updateConsent(
      {
        preferences: true,
        analytics: true,
        marketing: true,
      },
      "accept-all",
    );

    hideBanner();
    settingsButton.focus();
  });

  declineButton.addEventListener("click", () => {
    updateConsent(
      {
        preferences: false,
        analytics: false,
        marketing: false,
      },
      "decline-all",
    );

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
    updateConsent(
      {
        preferences: preferencesInput.checked,
        analytics: analyticsInput.checked,
        marketing: marketingInput.checked,
      },
      "save-preferences",
    );

    hideBanner();
    settingsButton.focus();
  });

  currentConsent = getConsent();

  if (currentConsent) {
    removeDeniedCookies(currentConsent);
  }

  if (currentConsent) {
    populatePreferences(currentConsent);
    hideBanner();
  } else {
    populatePreferences(null);
    showBanner();
  }

  exposeConsentApi();

  synchronizeConsentScripts();

  dispatchConsentEvent("cookie-consenter:ready", "initialization");
});
