import {HTTP} from './base'

export default {
  checkLogin: function () {
    return HTTP.get('api/app/user')
  },
  login: function (data) {
    return HTTP.post('oauth/response', data)
  },
  logout: function () {
    return HTTP.post('api/app/user/logout', {})
  }
}
