import {HTTP} from './base'
import {FormGenerator} from './form-generator'

export default {
  checkLogin: function () {
    return HTTP.get('api/app/user')
  },
  login: function (data) {
    return HTTP.post('oauth/response', FormGenerator.generateForm(data))
  },
  logout: function () {
    return HTTP.post('api/app/user/logout', {})
  }
}
