/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './index.html', // Vite's entry HTML file
    './src/**/*.{js,jsx,ts,tsx}', // Your React components and pages
  ],
  theme: {
    extend: {}, // Add customizations here
  },
  plugins: [], // Add plugins if needed
};
