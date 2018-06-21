import {HTTP} from './base'
import {FormGenerator} from './form-generator'
import {CONFIG} from '../CONFIG'
export default {
  getInfo: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=jobInfo', FormGenerator.generateForm(data))
  }
}
