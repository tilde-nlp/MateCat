import {HTTP} from './base'
import {FormGenerator} from './form-generator'
import {CONFIG} from '../CONFIG'
export default {
  getInfo: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=jobInfo', FormGenerator.generateForm(data))
  },
  setEditingTime: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=setEditingTime', FormGenerator.generateForm(data))
  },
  preTranslate: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=pretranslate', FormGenerator.generateForm(data))
  }
}
