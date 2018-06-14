import {HTTP} from './base'
export default {
  getList: function () {
    return HTTP.get('?action=getLanguages')
  },
  getSubjectsList: function () {
    return HTTP.get('?action=getSubjects')
  }
}
