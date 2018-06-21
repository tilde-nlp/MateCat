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
import VueTextareaAutosize from 'vue-textarea-autosize'
// Load Playfair Display typeface
require('typeface-playfair-display')

export function main (config) {
  Vue.config.productionTip = false

  Vue.prototype.$Alerts = AlertsObserver
  Vue.prototype.$Auth = Auth
  Vue.prototype.$CONFIG = config
  CONFIG.authRedirect = config.authRedirect
  CONFIG.baseUrl = config.baseUrl
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
  Vue.use(VueTextareaAutosize)
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