import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import lifeCounter from './life-counter';

window.Alpine = Alpine;
window.Chart = Chart;
Alpine.data('lifeCounter', lifeCounter);
Alpine.start();

if ('serviceWorker' in navigator) {
  window.addEventListener('load', async () => {
    try {
      const registration = await navigator.serviceWorker.register('/service-worker.js', {
        updateViaCache: 'none',
      });

      await registration.update();
    } catch {
      // O aplicativo continua funcional quando o navegador não oferece PWA.
    }
  });
}
