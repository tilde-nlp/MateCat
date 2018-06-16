<template>
  <div class="section-bg bg-grey-light">
    <section class="section">
      <div class="header-container">
        <span class="vam-helper" />
        <img
          :src="$store.state.profile.imageUrl"
          class="profile-img"
        >
        <div class="h-title">{{ $store.state.profile.fullName }}</div>
        <button
          class="button"
          @click="signOut()"
        >Iziet</button>
      </div>
    </section>
    <div class="bb-blueish"/>
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
