import {HTTP} from './base'

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
    return HTTP.post('?action=getVolumeAnalysis', data)
  },
  getList: function (data) {
    return HTTP.post('?action=getProjects', data)
  }
}
