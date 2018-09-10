export default {
  ready (state, ready) {
    state.ready = ready
  },
  isLoggedIn: (state, isLoggedIn) => {
    state.isLoggedIn = isLoggedIn
  },
  token: (state, token) => {
    state.token = token
  },
  authReady: (state, ready) => {
    state.authReady = ready
  },
  profile (state, profile) {
    state.profile = profile
    state.isLoggedIn = true
  },
  tmPretranslate (state, value) {
    state.profile.tm_pretranslate = value
  },
  mtPretranslate (state, value) {
    state.profile.mt_pretranslate = value
  },
  mtSystem (state, value) {
    state.mtSystem = value
  },
  termBaseUrl (state, termBaseUrl) {
    state.termBaseUrl = termBaseUrl
  },
  sourceSearch (state, sourceSearch) {
    state.sourceSearch = sourceSearch
  },
  targetSearch (state, targetSearch) {
    state.targetSearch = targetSearch
  },
  activeSegment (state, activeSegment) {
    state.unusedTags = null
    if (activeSegment === null) {
      state.unusedTags = []
    } else {
      state.unusedTags = []
    }
    state.activeSegment = activeSegment
  },
  fontSize (state, fontSize) {
    state.fontSize = fontSize
  }
}
