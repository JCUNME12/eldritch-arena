import "./bootstrap";
import Alpine from "alpinejs";

import lifeCounter from "./life-counter";
Alpine.data("lifeCounter", lifeCounter);
window.Alpine = Alpine;
Alpine.start();

if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker.register("/service-worker.js").catch(() => {});
    });
}
