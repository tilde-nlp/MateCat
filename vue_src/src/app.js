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
  CONFIG.baseUrl = config.baseUrl
  CONFIG.mtClientId = config.mtClientId
  CONFIG.mtBaseUrl = config.mtBaseUrl
  CONFIG.mtAppId = config.mtAppId
  CONFIG.assetPath = config.baseUrl + 'public/vue_dist/static/'
  Vue.prototype.$assetPath = config.baseUrl + 'public/vue_dist/static/'
  HTTP.defaults.baseUrl = config.baseUrl
  Vue.prototype.$HTTP = HTTP
  Vue.use(svgicon, {
    tagName: 'svgicon'
  })
  Vue.use(VueLocalStorage, {
    name: 'localStorage',
    bind: true
  })
  Vue.use(require('vue-shortkey'))
  Vue.use(require('vue-cookie'))
  Vue.use(require('vue-autosize'))
  const Lang = require('vuejs-localization')
  Lang.requireAll(require.context('../lang', true, /\.js$/))
  Vue.use(Lang)
  Vue.component('v-select', vSelect)
  window.$ = require('jquery')
  window.JQuery = require('jquery')
  /* eslint-disable no-new */
  new Vue({
    router,
    store,
    vueLoading: loading,
    el: '#app',
    created: function () {
      this.$loading.startLoading('app')
      this.$store.dispatch('init')
      const lang = this.$cookie.get('culture')
      switch (lang) {
        case 'en':
          this.$lang.setLang('en')
          break
        case 'ru':
          this.$lang.setLang('ru')
          break
        default:
          this.$lang.setLang('lv')
      }
    },
    render: h => h(App)
  })
}
