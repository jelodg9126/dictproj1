const CACHE_NAME = "dict-cache-v1";
const urlsToCache = [
  "/dictproj1/",
  "/dictproj1/index.php",
  "/dictproj1/public/Scripts/pwa-init.js"
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(async (cache) => {
      try {
        await cache.addAll(urlsToCache);
      } catch (err) {
        console.error("Cache install error:", err);
      }
    })
  );
});

self.addEventListener("fetch", (event) => {
  event.respondWith(
    caches.match(event.request).then((response) => {
      return response || fetch(event.request).catch(() => {
        return new Response("Offline", { status: 503 });
      });
    })
  );
});