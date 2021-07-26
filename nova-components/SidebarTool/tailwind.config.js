let novaConfig = require('./vendor/laravel/nova/tailwind.config.js')

module.exports = {
  ...novaConfig,
  mode: 'jit',
  purge: ['./src/**/*.php', './resources/**/*.{js,vue,blade.php}'],
};
