<template>
  <div class="footer-container">
    © Tilde
  </div>
</template>

<script>
export default {
  name: 'CustomFooter'
}
</script>

<style lang="less" scoped>
  @import (reference) "~less-entry";

  .footer-container {
    position: fixed;
    bottom: @spacer-4;
    right: @spacer-8;
    .size-xs;
    .dark;
    text-align: center;
    margin-top: @spacer-24;
  }
</style>
