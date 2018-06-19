import {HTTP} from './base'
import {FormGenerator} from './form-generator'
import {CONFIG} from '../CONFIG'
export default {
  checkLogin: function () {
    return HTTP.get(CONFIG.baseUrl + 'api/app/user')
  },
  login: function (data) {
    return HTTP.post(CONFIG.baseUrl + 'oauth/response', FormGenerator.generateForm(data))
  },
  logout: function () {
    return HTTP.post(CONFIG.baseUrl + 'api/app/user/logout', {})
  }
}
