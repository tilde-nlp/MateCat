import {HTTP} from './base'
import {FormGenerator} from './form-generator'

export default {
  upload: function (data) {
    return HTTP.post('lib/Utils/fileupload/', data)
  },
  convert: function (data) {
    return HTTP.post('?action=convertFile', data)
  },
  createProject: function (data) {
    return HTTP.post('?action=createProject&time=1527158705351,jid=undefined', data)
  },
  checkStatus: function (link) {
    return HTTP.get(link)
  },
  analyze: function (data) {
    return HTTP.post('?action=getVolumeAnalysis', FormGenerator.generateForm(data))
  },
  getList: function (data) {
    return HTTP.post('?action=getProjects', FormGenerator.generateForm(data))
  },
  delete: function (data) {
    return HTTP.post('?action=changeJobsStatus', FormGenerator.generateForm(data))
  },
  getUrls: function (data) {
    return HTTP.get('api/v2/projects/' + data.id_project + '/' + data.password + '/urls')
  }
}
