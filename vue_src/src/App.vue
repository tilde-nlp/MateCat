<template>
  <div>
    <transition
      name="ffade"
      mode="out-in">
      <div
        v-if="$loading.isLoading('app')"
        :key="1"
        class="splash-screen"
      >
        <img
          :src="$assetPath + 'splash-logo.svg'"
          class="splash-image mt-48 mb-16"
          height="200"
        >
        <img
          :src="$assetPath + 'loading.svg'"
          class="splash-image"
          height="48"
        >

      </div>
      <router-view
        v-else
        :key="2"
      />
    </transition>
  </div>
</template>

<script>
import {Alert} from '@shibetec/vue-toolbox'
import 'assets/inline-svg'
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
