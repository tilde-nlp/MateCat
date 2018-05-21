import {HTTP} from './base'

export default {
  upload: function (data) {
    return HTTP.post('lib/Utils/fileupload/', data, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  }
}
