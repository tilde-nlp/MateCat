import {HTTP} from './base'
import {CONFIG} from '../CONFIG'
import _ from 'lodash'
function getSystemStatus (metadata) {
  let status = null
  for (let i = 0; i < metadata.length; i++) {
    if (metadata[i].Key !== 'status') continue
    status = metadata[i].Value
    break
  }
  return status
}
export default {
  getList: function () {
    return HTTP.get(CONFIG.baseUrl + '?action=getLanguages')
  },
  getSubjectsList: function (lang) {
    return HTTP.get(CONFIG.mtBaseUrl + 'GetSystemList?appID=' + CONFIG.mtAppId + '&options=public&uiLanguageID=' + lang, {
      headers: { 'client-id': CONFIG.mtClientId }
    })
  },
  filterSystems: function (systems, from, to) {
    // Get relevant data for subjects dropdown
    let filteredSystems = _.filter(systems, el => {
      return el.SourceLanguage.Code === from &&
        el.TargetLanguage.Code === to &&
        getSystemStatus(el.Metadata) === 'running'
    })
    filteredSystems = _.map(filteredSystems, el => {
      return {
        label: el.Domain,
        value: el.ID
      }
    })
    return filteredSystems
  }
}
