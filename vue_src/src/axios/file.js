import {HTTP} from './base'

export default {
  upload: function (data) {
    return HTTP.post('lib/Utils/fileupload/', data, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },
  convert: function (data) {
    return HTTP.post('?action=convertFile', data, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },
  createProject: function (data) {
    return HTTP.post('?action=createProject&time=1527158705351,jid=undefined', data, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },
  checkStatus: function (link) {
    return HTTP.get(link)
  }
}
