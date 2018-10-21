<template>
  <span class="relative">
    <div
      class="translator-toolbox-link ib pointer relative"
      @click="() => { showOptions = !showOptions }"
    >
      <div
        v-if="icon"
        class="icon-container ib">
        <svgicon
          :name="icon"
          class="svg-icon va-middle"
          height="24"
        />
      </div>
      <div class="select-text ib">
        {{ title }}
      </div>
      <div
        class="icon-container ib arrow-down">
        <svgicon
          name="arrow-down"
          class="svg-icon va-middle"
          height="24"
        />
      </div>
    </div>
    <div
      v-if="showOptions"
      class="options translator-toolbox-link">
      <div class="option">Ar mašīntulku</div>
      <div class="option">Ar tulkošanas atmiņu</div>
    </div>
  </span>
</template>
<script>
export default {
  name: 'HugoSelect',
  props: {
    icon: {
      type: String,
      default: ''
    },
    title: {
      type: String,
      required: true
    }
  },
  data: function () {
    return {
      showOptions: false
    }
  }
}
</script>
<style lang="less">
  @import (reference) "~less-entry";

  .arrow-down {
    .absolute;
    top: 0;
    right: -22px;
  }
  .options {
    .absolute;
    background-color: @color-white;
    .b-blueish;
    z-index: 1000;
    min-width: 192px;
    .option {
      height: 48px;
      .border-box;
      margin-top: 12px;
      margin-left: 8px;
      .left;
      .va-middle;
    }
  }
</style>
