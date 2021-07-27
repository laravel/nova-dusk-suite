module.exports = {
  mode: 'jit',
  purge: ['./src/**/*.php', './resources/**/*.{js,vue,blade.php}'],
  important: '#sidebar-tool',
  ...require('./vendor/laravel/nova/tailwind.config.js'),
};
