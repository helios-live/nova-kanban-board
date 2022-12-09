/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./resources/**/*.{js,vue}"],
  prefix: "tw-",
  theme: {
    extend: {
      zIndex: {
        '100': '100',
        '50': '50',
        '44': '44',
      }
    },
  },
  plugins: [],
}
