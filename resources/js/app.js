import "./bootstrap";
import Alpine from "alpinejs";

import lifeCounter from "./life-counter";
import schemeDeck from "./scheme-deck";
Alpine.data("lifeCounter", lifeCounter);
Alpine.data("schemeDeck", schemeDeck);
window.Alpine = Alpine;
Alpine.start();

if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker.register("/service-worker.js").catch(() => {});
    });
}
