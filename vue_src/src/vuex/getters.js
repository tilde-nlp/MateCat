export default {
  stateReadyWatched: state => {
    return function () { return state.ready }
  },
  isLoggedIn: state => {
    console.log(state)
    return state.getters.isLoggedIn
  }
}
