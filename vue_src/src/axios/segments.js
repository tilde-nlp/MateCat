import {HTTP} from './base'
import {FormGenerator} from './form-generator'
export default {
  getSegments: function (data) {
    return HTTP.post('?action=getSegments', FormGenerator.generateForm(data))
  }
}
