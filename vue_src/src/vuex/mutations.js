import {TagsConverter} from 'utils/tags-converter'
import _ from 'lodash'
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
      const originalTags = TagsConverter.getTagList(activeSegment.original)
      const translationTags = TagsConverter.getTagList(activeSegment.translation)
      for (let i = 0; i < originalTags.length; i++) {
        const tag = _.find(translationTags, {id: originalTags[i].id})
        if (tag) {
          continue
        }
        state.unusedTags.push(originalTags[i])
      }
    }
    state.activeSegment = activeSegment
  },
  fontSize (state, fontSize) {
    state.fontSize = fontSize
  }
}
