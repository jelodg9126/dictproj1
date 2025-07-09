if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker
      .register("/dictproj1/public/Scripts/service-worker.js")
      .then(() => console.log("✅ Service Worker Registered"))
      .catch(err => console.error("❌ SW registration failed:", err));
  });
}

let deferredPrompt;

window.addEventListener("beforeinstallprompt", (e) => {
  e.preventDefault();
  deferredPrompt = e;
  console.log("📲 beforeinstallprompt captured");

  // Try to show the prompt after first interaction
  const promptInstall = () => {
    if (deferredPrompt) {
      deferredPrompt.prompt();
      deferredPrompt.userChoice.then(choice => {
        if (choice.outcome === "accepted") {
          console.log("✅ User accepted install prompt");
        } else {
          console.log("🚫 User dismissed install prompt");
        }
        deferredPrompt = null;
      });
    }
  };

  // Wait for any user gesture (scroll, click, etc)
  window.addEventListener("click", promptInstall, { once: true });
});
