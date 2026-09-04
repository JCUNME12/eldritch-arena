import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import lifeCounter from './life-counter';

window.Alpine = Alpine;
window.Chart = Chart;
Alpine.data('lifeCounter', lifeCounter);
Alpine.start();

if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/service-worker.js').catch(() => {});
  });
}
