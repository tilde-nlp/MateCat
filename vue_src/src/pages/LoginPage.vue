<template>
  <div class="page-container">
    <g-signin-button
      ref="loginButton"
      :params="googleSignInParams"
      class="button"
      @success="onSignInSuccess"
      @error="onSignInError">
      Pieslēgties
    </g-signin-button>
  </div>
</template>

<script>
export default {
  name: 'LoginPage',
  data () {
    return {
      /**
       * The Auth2 parameters, as seen on
       * https://developers.google.com/identity/sign-in/web/reference#gapiauth2initparams.
       * As the very least, a valid client_id must present.
       * @type {Object}
       */
      googleSignInParams: {
        client_id: this.$CONFIG.googleClientId
      },
      loginButton: ''
    }
  },
  mounted: function () {
    // console.log(this.$refs.loginButton)
    // this.$refs.loginButton.$el.click()
  },
  methods: {
    onSignInSuccess (googleUser) {
      // `googleUser` is the GoogleUser object that represents the just-signed-in user.
      // See https://developers.google.com/identity/sign-in/web/reference#users
      const profile = googleUser.getBasicProfile() // etc etc
      this.$store.commit('profile', profile)
      this.$router.push({name: 'file-list'})
    },
    onSignInError (error) {
      // `error` contains any error occurred.
      console.log('OH NOES', error)
    }
  }
}
</script>
