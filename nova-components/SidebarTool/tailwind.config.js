module.exports = {
  preset: [
    require('../../vendor/laravel/nova/tailwind.config.js')
  ],
  darkMode: 'class', // or 'media' or 'class'
  mode: 'jit',
  content: ['./src/**/*.php', './resources/**/*.{js,vue,blade.php}'],
};
