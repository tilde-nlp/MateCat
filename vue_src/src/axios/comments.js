import {HTTP} from './base'
import {FormGenerator} from './form-generator'
import {CONFIG} from '../CONFIG'
export default {
  doAction: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=comment&time=' + new Date().getTime() + ',jid=' + data.id_job + ',sid=' + data.id_segment, FormGenerator.generateForm(data))
  }
}
