<template>
  <div class="page-container">
    <button
      v-show="!$store.getters.profile"
      class="button"
      @click="googleSignIn"
    >Pieslēgties ar Google</button>
  </div>
</template>

<script>
import AuthService from '../axios/auth'
export default {
  name: 'LoginPage',
  created: function () {
    if (this.$store.getters.profile) this.$router.push({name: 'file-list'})
  },
  methods: {
    googleSignIn: function () {
      this.$gAuth.getAuthCode(authorizationCode => {
        // on success
        // eslint-disable-next-line no-undef
        let formData = new FormData()
        formData.append('code', authorizationCode)
        AuthService.login(formData)
          .then(response => {
            console.log('Succesful google login')
            console.log(response)
            this.$store.commit('profile', response.data)
            this.$router.push({name: 'file-list'})
          })
      }, error => {
        // on fail do something
        console.log(error)
      })
    }
  }
}
</script>
<style lang="less" scoped>
  @import (reference) "~less-entry";
  .page-container {
    max-width: 720px;
    margin: @spacer-128 auto;
    text-align: center;
    .button {
      line-height: @spacer-32;
    }
  }
</style>
