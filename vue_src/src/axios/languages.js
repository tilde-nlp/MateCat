import {HTTP} from './base'
import {CONFIG} from '../CONFIG'
export default {
  getList: function () {
    return HTTP.get(CONFIG.baseUrl + '?action=getLanguages')
  },
  getSubjectsList: function (lang) {
    return HTTP.get(CONFIG.mtBaseUrl + '&uiLanguageID=' + lang, {
      headers: { 'client-id': CONFIG.mtClientId }
    })
  },
  getMTSystems: function () {
    return HTTP.get(CONFIG.baseUrl + '?action=mtSystems')
  }
}
