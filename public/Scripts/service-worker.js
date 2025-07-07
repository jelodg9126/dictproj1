const CACHE_NAME = "dict-cache-v1";
const urlsToCache = [
  "/dictproj1/",
  "/dictproj1/index.php",
  "/dictproj1/public/Scripts/pwa-init.js"
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(urlsToCache))
  );
});

self.addEventListener("fetch", (event) => {
  event.respondWith(
    caches.match(event.request).then((response) => response || fetch(event.request))
  );
});