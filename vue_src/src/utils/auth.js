import Vue from 'vue'
import {store} from '../vuex/store'
import AuthService from '../axios/auth'

export const Auth = {
  checkAccess () {
    return store.state.isLoggedIn
  },
  login (email, pass, persist, cb) {
    cb = arguments[arguments.length - 1]
    AuthService.login(email, pass)
      .then(response => {
        store.commit('profile', {id: response.id, username: response.username})
        this.setToken(response.token, persist)
        if (cb) cb()
        store.commit('isLoggedIn', true)
      })
      .catch(e => {
        if (cb) cb(null)
        store.commit('isLoggedIn', false)
      })
  },
  checkLocalToken () {
    return new Promise((resolve, reject) => {
      if (!!Vue.localStorage.get('token') === true) {
        AuthService.tokenLogin(Vue.localStorage.get('token'))
          .then(response => {
            store.commit('access', response.accessList)
            store.commit('profile', {id: response.id, username: response.username})
            this.setToken(response.token, true)
            store.commit('isLoggedIn', true)
            resolve('')
          })
          .catch(e => {
            this.logout()
            resolve('')
          })
      } else {
        resolve('')
      }
    })
  },
  logout (cb) {
    Vue.localStorage.remove('token')
    AuthService.deleteToken()
    store.commit('isLoggedIn', false)
    store.commit('access', [])
    if (cb) cb()
  },
  setToken (token, persist) {
    persist = persist || false
    if (persist) Vue.localStorage.set('token', token)
    AuthService.setToken(token)
    store.commit('token', token)
  }
}
