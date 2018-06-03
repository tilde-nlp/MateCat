import {HTTP} from './base'
export default {
  getList: function () {
    return HTTP.get('?action=getLanguages')
  }
}
