let novaConfig = require('./vendor/laravel/nova/tailwind.config.js')

module.exports = {
  mode: 'jit',
  purge: ['./src/**/*.php', './resources/**/*.{js,vue,blade.php}'],
  theme: {
    extend: novaConfig.theme.extend
  },
  variants: {},
  plugins: []
};
