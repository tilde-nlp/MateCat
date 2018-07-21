export default {
  ready (state, ready) {
    if (state.debug) console.log('Setting ready: ' + ready)
    state.ready = ready
  },
  isLoggedIn: (state, isLoggedIn) => {
    if (state.debug) console.log('Setting isLoggedIn: ' + isLoggedIn)
    state.isLoggedIn = isLoggedIn
  },
  token: (state, token) => {
    if (state.debug) console.log('Setting token: ' + token)
    state.token = token
  },
  authReady: (state, ready) => {
    if (state.debug) console.log('Setting authReady: ' + ready)
    state.authReady = ready
  },
  profile (state, profile) {
    if (state.debug) {
      console.log('Setting profile: ')
      console.log(profile)
    }
    state.profile = profile
    state.isLoggedIn = true
  },
  sourceSearch (state, sourceSearch) {
    if (state.debug) {
      console.log('Setting sourceSearch: ')
      console.log(sourceSearch)
    }
    state.sourceSearch = sourceSearch
  },
  targetSearch (state, targetSearch) {
    if (state.debug) {
      console.log('Setting targetSearch: ')
      console.log(targetSearch)
    }
    state.targetSearch = targetSearch
  }
}
