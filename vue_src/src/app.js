import Vue from 'vue'
import App from './App.vue'
import svgicon from 'vue-svgicon'
import {AlertsObserver} from '@shibetec/vue-toolbox'
import {router} from './router/router'
import {HTTP} from './axios/base'
import {store} from './vuex/store'
import VueLocalStorage from 'vue-localstorage'
import {loading} from './utils/loading'
import {Auth} from './utils/auth'
import {CONFIG} from './config'
import GSignInButton from 'vue-google-signin-button'
// Load Playfair Display typeface
require('typeface-playfair-display')

Vue.config.productionTip = false

Vue.prototype.$Alerts = AlertsObserver
Vue.prototype.$HTTP = HTTP
Vue.prototype.$Auth = Auth
Vue.prototype.$CONFIG = CONFIG
Vue.prototype.$assetPath = CONFIG.apiUrl + 'public/vue_dist/static/'
Vue.use(svgicon, {
  tagName: 'svgicon'
})
Vue.use(GSignInButton)
Vue.use(VueLocalStorage, {
  name: 'localStorage',
  bind: true
})
/* eslint-disable no-new */
new Vue({
  router,
  store,
  vueLoading: loading,
  el: '#app',
  created: function () {
    this.$loading.startLoading('app')
    this.$store.dispatch('init')
  },
  render: h => h(App)
})
