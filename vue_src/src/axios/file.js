import {HTTP} from './base'
import {FormGenerator} from './form-generator'
import {CONFIG} from '../CONFIG'
export default {
  upload: function (data) {
    return HTTP.post(CONFIG.baseUrl + 'lib/Utils/fileupload/', data)
  },
  checkStatus: function (link) {
    return HTTP.get(link)
  },
  analyze: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=getVolumeAnalysis', FormGenerator.generateForm(data))
  },
  getList: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=getProjects', FormGenerator.generateForm(data))
  },
  delete: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=changeJobsStatus', FormGenerator.generateForm(data))
  },
  getUrls: function (data) {
    return HTTP.get(CONFIG.baseUrl + 'api/v2/projects/' + data.id_project + '/' + data.password + '/urls')
  }
}
