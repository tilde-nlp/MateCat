<template>
  <div
    :class="{active: segment.active}"
    class="segment-container font-size-0"
    @click="() => {$emit('click', segment.id)}"
  >
    <div
      :style="{ 'font-size': fontSizeString }"
      class="segment-original">
      {{ segment.original }}
    </div>
    <div
      v-shortkey.once="['ctrl', 'insert']"
      title="Kopēt orģinālu uz tulkojumu"
      class="segment-copy-icon"
      @click="copySourceToTarget"
      @shortkey="copySourceToTarget"
    >
      <svgicon
        class="svg-icon flip-h"
        name="arrow"
        height="24"
      />
    </div>
    <div class="segment-translation">
      <textarea-autosize
        ref="ta"
        v-model="segment.translation"
        :min-height="1"
        :style="{ 'font-size': fontSizeString }"
        :disabled="segment.status === 'done'"
        placeholder="Sākt tulkot..."
        class="segment-edit"
      />
    </div>
  </div>
</template>
<script>
export default {
  name: 'TranslatorSegment',
  props: {
    segmentData: {
      type: Object,
      required: true
    },
    fontSize: {
      type: Number,
      default: 15
    }
  },
  data: function () {
    return {
      segment: {},
      ta: ''
    }
  },
  computed: {
    fontSizeString: function () {
      return this.fontSize + 'px'
    }
  },
  watch: {
    fontSize () {
      this.$refs.ta.resize()
    }
  },
  mounted: function () {
    this.segment = this.segmentData
  },
  methods: {
    copySourceToTarget: function () {
      this.segment.translation = this.segment.original
    }
  }
}
</script>
