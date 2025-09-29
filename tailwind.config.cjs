// tailwind.config.cjs
module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/js/**/*.{vue,js,ts,jsx,tsx}',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree','ui-sans-serif','system-ui','-apple-system','Segoe UI','Roboto','Inter','sans-serif'],
      },
    },
  },
  plugins: [],
}
