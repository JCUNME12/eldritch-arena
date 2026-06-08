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
        display: ['Orbitron', 'Inter', ...defaultTheme.fontFamily.sans]
      },
      colors: {
        arena: {
          black: '#05030A',
          panel: '#0D0718',
          panel2: '#120A24',
          purple: '#A855F7',
          violet: '#7C3AED',
          cyan: '#22D3EE',
          gold: '#FBBF24'
        }
      },
      boxShadow: {
        neon: '0 0 28px rgba(168, 85, 247, 0.45)',
        cyan: '0 0 22px rgba(34, 211, 238, 0.25)'
      },
      backgroundImage: {
        'arena-radial': 'radial-gradient(circle at top left, rgba(168,85,247,.28), transparent 32%), radial-gradient(circle at bottom right, rgba(34,211,238,.18), transparent 28%), linear-gradient(135deg, #05030A 0%, #0D0718 45%, #030712 100%)'
      }
    }
  },
  plugins: [forms]
};
