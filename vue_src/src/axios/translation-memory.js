import {HTTP} from './base'
import {CONFIG} from '../CONFIG'
import {FormGenerator} from './form-generator'
export default {
  get: function () {
    return HTTP.get(CONFIG.baseUrl + '?action=getTranslationMemories')
  },
  saveSettings: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=saveSettings', FormGenerator.generateForm(data))
  }
}
