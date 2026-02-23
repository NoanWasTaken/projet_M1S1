/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./templates/**/*.html.twig",
    "./assets/**/*.{js,ts,jsx,tsx,css}",
    "./src/**/*.php",
  ],
  theme: {
    extend: {
      fontFamily: {
        gaming: ["var(--font-gaming)", "sans-serif"],
        ui: ["var(--font-ui)", "sans-serif"],
      },
    },
  },
  plugins: [],
};
