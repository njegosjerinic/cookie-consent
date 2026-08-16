const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const vm = require("node:vm");

class FakeElement {
  constructor(tagName = "div", attributes = {}) {
    this.tagName = tagName.toUpperCase();
    this.hidden = false;
    this.checked = false;
    this.textContent = "";
    this.dataset = {};
    this.attributes = [];
    this.listeners = {};
    this.replacement = null;
    this.async = false;
    this.defer = false;

    Object.entries(attributes).forEach(([name, value]) => {
      this.setAttribute(name, value);
    });
  }

  setAttribute(name, value) {
    const stringValue = String(value);
    const existing = this.attributes.find((attribute) => attribute.name === name);

    if (existing) {
      existing.value = stringValue;
    } else {
      this.attributes.push({ name, value: stringValue });
    }

    if (name.startsWith("data-")) {
      const key = name
        .slice(5)
        .replace(/-([a-z])/g, (_, character) => character.toUpperCase());
      this.dataset[key] = stringValue;
    }

    if (name === "type") this.type = stringValue;
    if (name === "async") this.async = true;
    if (name === "defer") this.defer = true;
  }

  addEventListener(name, callback) {
    this.listeners[name] = callback;
  }

  focus() {}

  replaceWith(element) {
    this.replacement = element;
  }
}

const ids = [
  "cookie-consenter-accept",
  "cookie-consenter-decline",
  "cookie-consenter-banner",
  "cookie-consenter-settings",
  "cookie-consenter-close",
  "cookie-consenter-manage",
  "cookie-consenter-preferences",
  "cookie-consenter-save-preferences",
  "cookie-consenter-category-preferences",
  "cookie-consenter-category-analytics",
  "cookie-consenter-category-marketing",
];

const elements = Object.fromEntries(ids.map((id) => [id, new FakeElement()]));
const placeholder = new FakeElement("script", {
  type: "text/plain",
  "data-cookie-consenter-script": "",
  "data-cookie-consenter-category": "analytics",
  "data-cookie-consenter-state": "blocked",
  "data-src": "https://example.com/analytics.js",
  defer: "",
});
const inlinePlaceholder = new FakeElement("script", {
  type: "text/plain",
  "data-cookie-consenter-script": "",
  "data-cookie-consenter-category": "marketing",
  "data-cookie-consenter-state": "blocked",
});
inlinePlaceholder.textContent = 'window.marketingLoaded = true;';
const invalidPlaceholder = new FakeElement("script", {
  type: "text/plain",
  "data-cookie-consenter-script": "",
  "data-cookie-consenter-category": "invalid",
  "data-cookie-consenter-state": "blocked",
  "data-src": "https://example.com/invalid.js",
});
const placeholders = [placeholder, inlinePlaceholder, invalidPlaceholder];
const documentListeners = {};

const expiredCookies = [];
global.document = {
  getElementById: (id) => elements[id] ?? null,
  querySelectorAll: () => placeholders.filter((item) => !item.replacement),
  createElement: (tagName) => new FakeElement(tagName),
  addEventListener(name, callback) {
    documentListeners[name] ??= [];
    documentListeners[name].push(callback);
  },
  dispatchEvent(event) {
    (documentListeners[event.type] ?? []).forEach((callback) => callback(event));
  },
};
Object.defineProperty(global.document, "cookie", {
  get: () =>
    "_ga=GA1.1.123; _ga_TEST=GS1.1.456; _fbp=fb.1.123.456; _fbc=fb.1.123.click; unrelated=keep",
  set: (value) => expiredCookies.push(value),
});

const storage = new Map();
global.localStorage = {
  getItem: (key) => storage.get(key) ?? null,
  setItem: (key, value) => storage.set(key, value),
  removeItem: (key) => storage.delete(key),
};
global.CustomEvent = class CustomEvent {
  constructor(type, options) {
    this.type = type;
    this.detail = options.detail;
  }
};
let reloadCount = 0;
global.window = {
  CookieConsenterConfig: {
    policyVersion: "1",
    consentDurationDays: 180,
  },
  location: {
    hostname: "www.example.com",
    reload() {
      reloadCount += 1;
    },
  },
};

const frontendPath = path.join(__dirname, "..", "assets", "js", "frontend.js");
vm.runInThisContext(fs.readFileSync(frontendPath, "utf8"), {
  filename: frontendPath,
});

documentListeners.DOMContentLoaded[0]();

assert.equal(window.CookieConsenter.hasConsent("analytics"), false);
assert.equal(placeholder.replacement, null);

elements["cookie-consenter-accept"].listeners.click();

assert.equal(window.CookieConsenter.hasConsent("analytics"), true);
assert.ok(placeholder.replacement, "Analytics placeholder should be replaced.");
assert.equal(placeholder.replacement.src, "https://example.com/analytics.js");
assert.equal(placeholder.replacement.defer, true);
assert.equal(
  inlinePlaceholder.replacement.textContent,
  'window.marketingLoaded = true;',
);
assert.equal(invalidPlaceholder.replacement, null);

const publicConsent = window.CookieConsenter.getConsent();
publicConsent.choices.analytics = false;
assert.equal(window.CookieConsenter.hasConsent("analytics"), true);

elements["cookie-consenter-decline"].listeners.click();
assert.equal(reloadCount, 1, "Withdrawing active consent should reload once.");
assert.ok(
  expiredCookies.some((cookie) => cookie.startsWith("_ga=")),
  "Declining analytics must expire the Google Analytics cookie.",
);
assert.ok(
  expiredCookies.some((cookie) => cookie.startsWith("_ga_TEST=")),
  "Declining analytics must expire Google Analytics property cookies.",
);
assert.ok(
  expiredCookies.some((cookie) => cookie.startsWith("_fbp=")),
  "Declining marketing must expire the Meta browser identifier cookie.",
);
assert.ok(
  expiredCookies.some((cookie) => cookie.startsWith("_fbc=")),
  "Declining marketing must expire the Meta click identifier cookie.",
);
assert.equal(
  expiredCookies.some((cookie) => cookie.startsWith("unrelated=")),
  false,
  "Unrelated cookies must not be removed.",
);

elements["cookie-consenter-accept"].listeners.click();
assert.equal(
  reloadCount,
  2,
  "Granting previously denied consent should reload the page.",
);

console.log("Consent frontend tests passed.");
