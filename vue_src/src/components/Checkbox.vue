<template>
  <div
    :class="{disabled: disabled}"
    class="checkbox-container"
    @click="change"
  >
    <img
      v-if="isOn"
      :src="$assetPath + 'on.svg'"
      height="24"
    >
    <img
      v-if="!isOn"
      :src="$assetPath + 'off.svg'"
      height="24"
    >
  </div>
</template>
<script>
export default {
  name: 'CheckBox',
  props: {
    value: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },
  data: function () {
    return {
      isOn: false
    }
  },
  mounted: function () {
    this.isOn = this.value
  },
  methods: {
    change: function () {
      if (this.disabled) {
        return
      }
      this.isOn = !this.isOn
      this.$emit('change', this.isOn)
    }
  }
}
</script>
