import {HTTP} from './base'
import {FormGenerator} from './form-generator'
import {CONFIG} from '../CONFIG'
export default {
  add: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=comment&time=' + new Date().getTime() + ',jid=' + data.id_job + ',sid=' + data.id_segment, FormGenerator.generateForm(data))
  },
  getList: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=comment&time=' + new Date().getTime() + ',jid=' + data.id_job, FormGenerator.generateForm(data))
  }
}
