<template>
  <div class="header-container">
    <img :src="$store.state.profile.getImageUrl()">
    Hello, {{ $store.state.profile.getName() }}
    <button @click="signOut()">Sign out</button>
  </div>
</template>

<script>
export default {
  name: 'CustomHeader',
  methods: {
    signOut: function () {
      const auth2 = window.gapi.auth2.getAuthInstance()
      auth2.signOut().then(() => {
        this.$store.commit('isLoggedIn', false)
        this.$store.commit('profile', false)
        this.$router.push({name: 'login'})
      })
    }
  }
}
</script>

<style lang="less" scoped>
  @import (reference) "~less-entry";

  .header-container {
    width: 100%;
    position: relative;
    margin-left: auto;
    margin-right: auto;
    text-align: center;
    margin-bottom: @spacer-32;
    .size-m;
    .dark;
  }
</style>
