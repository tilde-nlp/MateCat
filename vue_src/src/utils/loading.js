import VueLoading from 'vuex-loading'
import Vue from 'vue'

Vue.use(VueLoading)

export const loading = new VueLoading({useVuex: true, moduleName: 'loading'})
