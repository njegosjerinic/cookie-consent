document.addEventListener("DOMContentLoaded", () => {
  let acceptButton = document.getElementById("cookieAccept");
  let declineButton = document.getElementById("cookieDecline");
  let banner = document.getElementById("banner");

  if (localStorage.getItem("permission") == "accepted") {
    banner.style.display = "none";
  }

  if (localStorage.getItem("permission") == "declined") {
    banner.style.display = "none";
  }

  acceptButton.addEventListener("click", () => {
    banner.style.display = "none";
    localStorage.setItem("permission", "accepted");
  });

  declineButton.addEventListener("click", () => {
    banner.style.display = "none";
    localStorage.setItem("permission", "declined");
  });
});
