import Vue from 'vue'
export const State = {
  openSelect: '',
  setActiveSelect: function (id) {
    Vue.set(this, 'openSelect', id)
  }
}
