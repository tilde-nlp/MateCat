import { getTagList } from 'utils/segment/segment-text'

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
    state.profile.tm_pretranslate = parseInt(profile.tm_pretranslate) > 0
    state.profile.mt_pretranslate = parseInt(profile.mt_pretranslate) > 0
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
  termSearch (state, value) {
    state.termSearch = value
  },
  termBaseUrl (state, termBaseUrl) {
    state.termBaseUrl = termBaseUrl
  },
  synonymBaseUrl (state, synonymBaseUrl) {
    state.synonymBaseUrl = synonymBaseUrl
  },
  sourceSearch (state, sourceSearch) {
    state.sourceSearch = sourceSearch
  },
  targetSearch (state, targetSearch) {
    state.targetSearch = targetSearch
  },
  recalculateUnusedTags (state, dud) {
    state.unusedTags = null
    state.unusedTags = []
    if (state.activeSegment !== null) {
      const allTags = getTagList(state.activeSegment.original, state.activeSegment.id)
      const existingTags = getTagList(state.activeSegment.translation, state.activeSegment.id)
      const existingIds = []
      existingTags.forEach(el => {
        existingIds.push(el.id)
      })
      allTags.forEach(el => {
        if (existingIds.indexOf(el.id) < 0) {
          state.unusedTags.push(el)
        }
      })
    }
  },
  activeSegment (state, activeSegment) {
    state.activeSegment = activeSegment
    state.unusedTags = null
    state.unusedTags = []
    if (activeSegment !== null) {
      const allTags = getTagList(activeSegment.original, activeSegment.id)
      const existingTags = getTagList(activeSegment.translation, activeSegment.id)
      const existingIds = []
      existingTags.forEach(el => {
        existingIds.push(el.id)
      })
      allTags.forEach(el => {
        if (existingIds.indexOf(el.id) < 0) {
          state.unusedTags.push(el)
        }
      })
    }
  },
  fontSize (state, fontSize) {
    state.fontSize = fontSize
  }
}
