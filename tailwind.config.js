/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#84dcc6',
        'lighter-primary': '#a5ffd6',
        'darker-primary': '#6eaf9f',
        secondary: '#ff686b',
        'lighter-secondary': '#ffa69e',
        'darker-secondary': '#cc494b',
        tertiary: '#FF5666',
        quartyary: '#d0e2ff',
        'space-black': '#22223B'
      },
      fontFamily: {
        inter: ['Inter']
      },
      letterSpacing: {
        xtrawide: '0.15em',
        superwide: '0.2em'
      }
    },
  },
  plugins: [],
}