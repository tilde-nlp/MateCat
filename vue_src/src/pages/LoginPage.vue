<template>
  <div class="margin-center center mt-128">
    <button
      v-show="!$store.getters.profile"
      class="button"
      @click="googleSignIn"
    >Pieslēgties</button>
  </div>
</template>

<script>
import AuthService from 'services/auth'
export default {
  name: 'LoginPage',
  created: function () {
    if (this.$store.getters.profile) this.$router.push({name: 'file-list'})
  },
  methods: {
    googleSignIn: function () {
      this.$gAuth.getAuthCode(authorizationCode => {
        AuthService.login({code: authorizationCode})
          .then(response => {
            this.$store.commit('profile', response.data)
            this.$router.push({name: 'file-list'})
          })
      }, error => {
        // TODO Handle login error (is it necessary if auth will be switched to Hugo's?)
        console.log(error)
      })
    }
  }
}
</script>
