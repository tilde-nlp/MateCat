<template>
  <div>
    <div class="alerts">
      <transition-group
        name="fade"
        mode="out-in">
        <alert
          v-for="alert in alerts"
          :key="alert.id"
          @ok="removeAlert(alert.id)"
        >{{ alert.text }}</alert>
      </transition-group>
    </div>
    <transition
      name="fade"
      mode="out-in">
      <router-view />
    </transition>
  </div>
</template>

<script>
import {Alert} from '@shibetec/vue-toolbox'
import './assets/inline-svg'
export default {
  name: 'App',
  components: {
    'alert': Alert
  },
  data: function () {
    return {
      alerts: []
    }
  },
  mounted: function () {
    this.$Alerts.registerListener(this)
  },
  methods: {
    newAlert: function (alert) {
      this.alerts.push(alert)
    },
    removeAlert: function (id) {
      this.alerts = this.alerts.filter(el => { return el.id !== id })
    }
  }
}
</script>

<style lang="less">
  @import "~less-entry";
</style>
