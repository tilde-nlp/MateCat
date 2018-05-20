import {HTTP} from './base'

export default {
  checkLogin: function () {
    return HTTP.get('api/app/user')
  },
  register: function (data) {
    return HTTP.post('auths/register', data)
  },
  login: function (username, password) {
    return HTTP.post('auths/login', {
      username: username,
      password: password
    })
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
