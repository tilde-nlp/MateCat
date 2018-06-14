export default {
  stateReadyWatched: state => {
    return function () { return state.ready }
  },
  isLoggedIn: state => {
    return state.isLoggedIn
  },
  profile: state => {
    return state.profile
  }
}
