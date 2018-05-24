<template>
  <div class="header-container">
    <img :src="$store.state.profile.imageUrl">
    Hello, {{ $store.state.profile.fullName }}
    <button @click="signOut()">Sign out</button>
  </div>
</template>

<script>
import AuthService from '../../axios/auth'
export default {
  name: 'CustomHeader',
  methods: {
    signOut: function () {
      this.$gAuth.signOut(() => {
        AuthService.logout()
        this.$store.commit('isLoggedIn', false)
        this.$store.commit('profile', false)
        this.$router.push({name: 'login'})
      }, error => {
        console.log('Sing out error')
        console.log(error)
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
