import {HTTP} from './base'
import {FormGenerator} from './form-generator'
export default {
  getSegments: function (data) {
    return HTTP.post('?action=getSegments', FormGenerator.generateForm(data))
  },
  setTranslation: function (data) {
    return HTTP.post('?action=setTranslation', FormGenerator.generateForm(data))
  },
  getContribution: function (data) {
    return HTTP.post('?action=getContribution', FormGenerator.generateForm(data))
  }
}
