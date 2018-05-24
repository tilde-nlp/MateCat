import {store} from '../vuex/store'

export const Auth = {
  checkAccess () {
    return store.state.isLoggedIn
  }
}
