import {HTTP} from './base'
import {CONFIG} from '../CONFIG'
export default {
  getList: function () {
    return HTTP.get(CONFIG.baseUrl + '?action=getLanguages')
  },
  getSubjectsList: function (lang) {
    return HTTP.get('https://hugokisc.tilde.lv/ws/Service.svc/json/GetSystemList?appID=hugo.lv&uiLanguageID=' + lang + '&options=public', {
      headers: { 'client-id': 'u-9ab0841f-1bbf-4cc4-9323-bd5bbddb4f3a' }
    })
  },
  getMTSystems: function () {
    return HTTP.get(CONFIG.baseUrl + '?action=mtSystems')
  }
}
