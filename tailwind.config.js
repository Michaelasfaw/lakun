/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    './views/**/*.php',
    './assets/js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        // Brand — warm saffron/amber
        amber: {
          DEFAULT: '#D97706',
          50:  '#FFF9EB',
          100: '#FEF0C7',
          200: '#FDDF8A',
          300: '#FCC84D',
          400: '#FBB024',
          500: '#F59A0B',
          600: '#DD7A06',
          700: '#B75A09',
          800: '#94460E',
          900: '#7A3A0F',
        },
        // Warm neutrals replace the cool default grays across every view
        gray: {
          50:  '#FAF9F7',
          100: '#F3F1EC',
          200: '#E8E5DE',
          300: '#D6D1C7',
          400: '#A49E93',
          500: '#7B756B',
          600: '#5D5850',
          700: '#47433D',
          800: '#2E2B27',
          900: '#1C1A17',
        },
        // Ink — dark surfaces (sidebar + dark mode)
        charcoal: {
          DEFAULT: '#121110',
          light:   '#1B1A17',
          lighter: '#2C2A26',
          muted:   '#6E685F',
        },
        ink: {
          DEFAULT: '#14120F',
          800: '#1C1A16',
          700: '#26231E',
          600: '#34302A',
        },
        cream: {
          DEFAULT: '#F7F5F0',
          dark:    '#EEEAE1',
        },
      },
      fontFamily: {
        sans:    ['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif'],
        display: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif'],
        mono:    ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'monospace'],
      },
      boxShadow: {
        xs:   '0 1px 2px 0 rgb(28 25 23 / 0.05)',
        sm:   '0 1px 2px 0 rgb(28 25 23 / 0.04), 0 1px 3px 0 rgb(28 25 23 / 0.05)',
        DEFAULT: '0 1px 3px 0 rgb(28 25 23 / 0.06), 0 1px 2px -1px rgb(28 25 23 / 0.05)',
        md:   '0 6px 16px -4px rgb(28 25 23 / 0.08), 0 2px 4px -2px rgb(28 25 23 / 0.04)',
        lg:   '0 14px 32px -8px rgb(28 25 23 / 0.12), 0 4px 8px -4px rgb(28 25 23 / 0.05)',
        xl:   '0 24px 48px -12px rgb(28 25 23 / 0.18), 0 8px 16px -8px rgb(28 25 23 / 0.06)',
        '2xl':'0 32px 64px -16px rgb(28 25 23 / 0.25)',
        card: '0 0 0 1px rgb(28 25 23 / 0.05), 0 1px 2px 0 rgb(28 25 23 / 0.04), 0 2px 6px -1px rgb(28 25 23 / 0.04)',
        glow: '0 8px 24px -6px rgb(221 122 6 / 0.45)',
        inner: 'inset 0 1px 2px 0 rgb(28 25 23 / 0.06)',
      },
      borderRadius: {
        '4xl': '2rem',
      },
      letterSpacing: {
        tightest: '-0.035em',
      },
      animation: {
        'fade-slide-up':   'fadeSlideUp 0.5s cubic-bezier(.2,.8,.2,1)',
        'slide-in-right':  'slideInRight 0.35s cubic-bezier(.2,.8,.2,1)',
        'slide-out-right': 'slideOutRight 0.3s ease-in forwards',
        'fade-in':         'fadeIn 0.2s ease-out',
        'pulse-dot':       'pulseDot 1.5s ease-in-out infinite',
        'bounce-in':       'bounceIn 0.4s cubic-bezier(0.36, 0.07, 0.19, 0.97)',
        'card-appear':     'cardAppear 0.35s cubic-bezier(.2,.8,.2,1) both',
        'spin':            'spin 1s linear infinite',
        'ping-slow':       'ping 2s cubic-bezier(0, 0, 0.2, 1) infinite',
        'float':           'float 6s ease-in-out infinite',
      },
      keyframes: {
        slideInRight:  { from: { transform: 'translateX(100%)', opacity: '0' }, to: { transform: 'translateX(0)', opacity: '1' } },
        slideOutRight: { from: { transform: 'translateX(0)', opacity: '1' }, to: { transform: 'translateX(100%)', opacity: '0' } },
        fadeIn:        { from: { opacity: '0' }, to: { opacity: '1' } },
        pulseDot:      { '0%,100%': { transform: 'scale(1)', opacity: '1' }, '50%': { transform: 'scale(1.4)', opacity: '0.7' } },
        bounceIn:      { '0%': { transform: 'scale(0.8)', opacity: '0' }, '80%': { transform: 'scale(1.05)' }, '100%': { transform: 'scale(1)', opacity: '1' } },
        cardAppear:    { from: { transform: 'translateY(10px)', opacity: '0' }, to: { transform: 'translateY(0)', opacity: '1' } },
        fadeSlideUp:   { from: { transform: 'translateY(16px)', opacity: '0' }, to: { transform: 'translateY(0)', opacity: '1' } },
        spin:          { from: { transform: 'rotate(0deg)' }, to: { transform: 'rotate(360deg)' } },
        float:         { '0%,100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-8px)' } },
      },
    },
  },
  plugins: [],
}
