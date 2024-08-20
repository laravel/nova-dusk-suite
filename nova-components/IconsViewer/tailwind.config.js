module.exports = {
  preset: [
    require('./vendor/laravel/nova/tailwind.config.js')
  ],
  darkMode: 'class', // or 'media' or 'class'
  purge: false,
  theme: {
    extend: {}
  },
  variants: {},
  plugins: []
};
