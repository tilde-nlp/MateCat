import CleanState from './state'
import {loading} from '../utils/loading'
import AuthService from '../axios/auth'

export default {
  init: state => {
    state.state = CleanState
    AuthService.checkLogin()
      .then(r => {
        loading.endLoading('app')
        state.commit('ready', true)
      })
      .catch(e => {
        loading.endLoading('app')
        state.commit('ready', true)
      })
  },
  signOut: state => {
    state.state = CleanState
  }
}
