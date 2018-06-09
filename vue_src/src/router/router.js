import VueRouter from 'vue-router'
import Vue from 'vue'
import FileList from 'pages/FileList'
import Translator from 'pages/Translator'
import LoginPage from 'pages/LoginPage'
import {store} from '../vuex/store'
import {Auth} from '../utils/auth'
Vue.use(VueRouter)
const routes = [
  {path: '/', component: FileList, name: 'file-list', beforeEnter: beforeRouteEnter},
  {path: '/translate/:jobId/:password', component: Translator, name: 'translate', beforeEnter: beforeRouteEnter},
  {path: '/login', component: LoginPage, name: 'login'},
  {path: '*', redirect: {name: 'file-list'}}
]
function beforeRouteEnter (to, from, next) {
  const toLogin = {
    path: '/login',
    query: {redirect: to.fullPath}
  }
  if (store.state.ready === false) {
    const watcher = store.watch(store.getters.stateReadyWatched, ready => {
      watcher() // stop watching
      if (Auth.checkAccess()) next()
      else next(toLogin)
    })
  } else if (Auth.checkAccess()) next()
  else next(toLogin)
}
export const router = new VueRouter({
  routes
})
