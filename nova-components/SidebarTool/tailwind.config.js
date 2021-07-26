module.exports = {
  ...require('./vendor/laravel/nova/tailwind.config.js'),
  mode: 'jit',
  purge: ['./src/**/*.php', './resources/**/*.{js,vue,blade.php}'],
};
