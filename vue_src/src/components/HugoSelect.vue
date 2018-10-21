<template>
  <span class="relative">
    <div
      class="translator-toolbox-link ib pointer relative"
      @click="toggleOptions"
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
      v-if="state.openSelect === _uid"
      :id="'options-container-' + _uid"
      class="options translator-toolbox-link animated faster fadeIn">
      <div
        v-for="(option, index) in options"
        :key="index +1"
        class="option"
        @click="select(option.id)"
      >{{ option.value }}</div>
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
    },
    options: {
      type: Array,
      required: true
    }
  },
  data: function () {
    return {
      showOptions: false,
      state: this.$state
    }
  },
  methods: {
    toggleOptions: function () {
      if (this.state.openSelect !== this._uid) {
        this.showOptions = true
      } else {
        this.showOptions = !this.showOptions
      }
      if (this.showOptions) {
        this.$state.setActiveSelect(this._uid)
      } else {
        this.closeOptions()
      }
    },
    closeOptions: function () {
      this.showOptions = false
      this.$state.setActiveSelect('')
    },
    select: function (id) {
      this.$emit('select', id)
      this.closeOptions()
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
    top: 27px;
    left: 0px;
    padding-bottom: 12px;
    .option {
      height: 36px;
      .border-box;
      margin-top: 12px;
      margin-left: 8px;
      .left;
      .va-middle;
    }
  }
</style>
