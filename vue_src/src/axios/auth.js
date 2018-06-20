import {HTTP} from './base'
import {CONFIG} from '../CONFIG'
export default {
  checkLogin: function () {
    return HTTP.get(CONFIG.baseUrl + 'api/app/user')
  }
}
