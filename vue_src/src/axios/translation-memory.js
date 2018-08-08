import {HTTP} from './base'
import {CONFIG} from '../CONFIG'
export default {
  get: function () {
    return HTTP.get(CONFIG.baseUrl + '?action=getTranslationMemories')
  }
}
