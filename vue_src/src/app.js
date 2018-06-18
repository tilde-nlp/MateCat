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
import GAuth from 'vue-google-oauth2'
import vSelect from 'vue-select'
import {CONFIG} from './CONFIG'
// Load Playfair Display typeface
require('typeface-playfair-display')

export function main (config) {
  Vue.config.productionTip = false

  Vue.prototype.$Alerts = AlertsObserver
  Vue.prototype.$Auth = Auth
  Vue.prototype.$CONFIG = config
  CONFIG.authRedirect = config.authRedirect
  Vue.prototype.$assetPath = config.baseUrl + 'public/vue_dist/static/'
  HTTP.defaults.baseUrl = config.baseUrl
  Vue.prototype.$HTTP = HTTP
  Vue.use(svgicon, {
    tagName: 'svgicon'
  })
  Vue.use(GAuth, {clientId: config.googleClientId, scope: 'profile email https://www.googleapis.com/auth/plus.login'})
  Vue.use(VueLocalStorage, {
    name: 'localStorage',
    bind: true
  })
  Vue.use(require('vue-shortkey'))
  Vue.component('v-select', vSelect)
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
}
