document.addEventListener("DOMContentLoaded", () => {
  const STORAGE_KEY = "cookie-consenter-consent";
  const CONSENT_VERSION = 1;

  const acceptButton = document.getElementById("cookie-consenter-accept");
  const declineButton = document.getElementById("cookie-consenter-decline");
  const banner = document.getElementById("cookie-consenter-banner");

  const hideBanner = () => {
    banner.style.display = "none";
  };

  if (!acceptButton || !declineButton || !banner) {
    return;
  }

  const getConsent = () => {
    try {
      const storedConsent = localStorage.getItem(STORAGE_KEY);

      if (!storedConsent) {
        return null;
      }

      const consent = JSON.parse(storedConsent);
      const validStatuses = ["accepted", "declined"];

      if (
        consent.version !== CONSENT_VERSION ||
        !validStatuses.includes(consent.status)
      ) {
        return null;
      }

      return consent;
    } catch (error) {
      return null;
    }
  };

  const saveConsent = (status) => {
    const consent = {
      version: CONSENT_VERSION,
      status,
      updatedAt: new Date().toISOString(),
    };

    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(consent));
    } catch (error) {}
  };

  if (getConsent()) {
    hideBanner();
  }

  acceptButton.addEventListener("click", () => {
    saveConsent("accepted");
    hideBanner();
  });

  declineButton.addEventListener("click", () => {
    saveConsent("declined");
    hideBanner();
  });
});
