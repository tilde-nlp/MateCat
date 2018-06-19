import {HTTP} from './base'
import {FormGenerator} from './form-generator'
import {CONFIG} from '../CONFIG'
export default {
  getSegments: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=getSegments', FormGenerator.generateForm(data))
  },
  setTranslation: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=setTranslation', FormGenerator.generateForm(data))
  },
  getContribution: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=getContribution', FormGenerator.generateForm(data))
  }
}
