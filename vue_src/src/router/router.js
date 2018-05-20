import VueRouter from 'vue-router'
import Vue from 'vue'
import FileList from 'pages/FileList'
import Translator from 'pages/Translator'
Vue.use(VueRouter)
const routes = [
  {path: '/', component: FileList, name: 'file-list'},
  {path: '/translate', component: Translator, name: 'translate'},
  {path: '*', redirect: {name: 'file-list'}}
]
export const router = new VueRouter({
  routes
})
