import VueRouter from 'vue-router'
import Vue from 'vue'
import FileList from 'pages/FileList'
import Translator from 'pages/Translator'
import {store} from '../vuex/store'
import {Auth} from '../utils/auth'
import {CONFIG} from '../CONFIG'
Vue.use(VueRouter)
const routes = [
  {path: '/', component: FileList, name: 'file-list', beforeEnter: beforeRouteEnter},
  {path: '/translate/:jobId/:password', component: Translator, name: 'translate', beforeEnter: beforeRouteEnter},
  {path: '*', redirect: {name: 'file-list'}}
]
function beforeRouteEnter (to, from, next) {
  const redirect = CONFIG.authRedirect + encodeURIComponent(window.location.href + to.fullPath)
  if (store.state.ready === false) {
    const watcher = store.watch(store.getters.stateReadyWatched, ready => {
      watcher() // stop watching
      if (Auth.checkAccess()) next()
      else window.location.replace(redirect)
    })
  } else if (Auth.checkAccess()) next()
  else window.location.replace(redirect)
}
export const router = new VueRouter({
  routes
})
