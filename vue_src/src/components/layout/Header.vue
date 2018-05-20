<template>
  <div class="header-container"/>
</template>

<script>
export default {
  name: 'CustomHeader'
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
    margin-top: @spacer-32;
  }
</style>
