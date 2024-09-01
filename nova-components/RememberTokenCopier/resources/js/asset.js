import RememberTokenCopier from './components/RememberTokenCopier'

const Nova = window.Nova

Nova.booting(app => {
  Nova.component('remember-token-copier', RememberTokenCopier)
})
