import CleanState from './state'
import {loading} from '../utils/loading'
import AuthService from '../axios/auth'

export default {
  init: state => {
    state.state = CleanState
    AuthService.checkLogin()
      .then(r => {
        if (r.data.hasOwnProperty('user')) {
          let profile = r.data.user
          profile['fullName'] = profile.first_name + ' ' + profile.last_name
          profile['imageUrl'] = r.data.metadata.gplus_picture
          profile['teamId'] = r.data.teams[0].id
          state.commit('profile', r.data.user)
        }
        loading.endLoading('app')
        state.commit('ready', true)
      })
      .catch(e => {
        loading.endLoading('app')
        state.commit('ready', true)
      })
  }
}
