import Vue from 'vue'
import App from './App.vue'
import svgicon from 'vue-svgicon'
import {AlertsObserver} from '@shibetec/vue-toolbox'
import {router} from './router/router'
// Load Playfair Display typeface
require('typeface-playfair-display')

Vue.config.productionTip = false

Vue.prototype.$Alerts = AlertsObserver
Vue.use(svgicon, {
  tagName: 'svgicon'
})

/* eslint-disable no-new */
new Vue({
  router,
  el: '#app',
  render: h => h(App)
})
