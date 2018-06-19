import {HTTP} from './base'
import {CONFIG} from '../CONFIG'
export default {
  getList: function () {
    return HTTP.get(CONFIG.baseUrl + '?action=getLanguages')
  },
  getSubjectsList: function () {
    return HTTP.get(CONFIG.baseUrl + '?action=getSubjects')
  }
}
