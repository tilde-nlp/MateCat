import {HTTP} from './base'

export default {
  checkLogin: function () {
    return HTTP.get('api/app/user')
  },
  login: function (data) {
    return HTTP.post('oauth/response', data)
  },
  tokenLogin: function (token) {
    return HTTP.post('auths/token-login', {token: token})
  },
  logout: function () {
    return HTTP.post('auths/logout', {})
  },
  setToken: function (token) {
    HTTP.defaults.headers.common['AuthToken'] = token
  },
  deleteToken: function () {
    delete HTTP.defaults.headers.common['AuthToken']
  }
}
