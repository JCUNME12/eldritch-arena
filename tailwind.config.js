import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

export default {
  darkMode: 'class',
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js'
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', ...defaultTheme.fontFamily.sans],
        display: ['Georgia', ...defaultTheme.fontFamily.serif]
      },
      colors: {
        arena: {
          black: '#10151c',
          panel: '#191f28',
          panel2: '#202735',
          purple: '#b7a1d5',
          violet: '#716082',
          cyan: '#83bcb3',
          gold: '#d7bc80'
        }
      },
      boxShadow: {
        neon: '0 0 28px rgba(168, 85, 247, 0.45)',
        cyan: '0 0 22px rgba(34, 211, 238, 0.25)'
      },
      backgroundImage: {
        'arena-radial': 'radial-gradient(circle at top left, rgba(168,85,247,.28), transparent 32%), radial-gradient(circle at bottom right, rgba(34,211,238,.18), transparent 28%), linear-gradient(135deg, #10151c 0%, #191f28 45%, #030712 100%)'
      }
    }
  },
  plugins: [forms]
};
