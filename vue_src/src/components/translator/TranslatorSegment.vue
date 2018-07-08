<template>
  <div
    :class="{active: segment.active, confirmed: segment.status === 'done'}"
    class="segment-container font-size-0"
    @click="() => {$emit('click', segment.id)}"
  >
    <div class="double-block segment-fix">
      <div
        :class="{top: topSegment}"
        class="number-col bl-light-darker">
        <div class="ma">{{ nr }}</div>
      </div>
      <div
        :style="{ 'font-size': fontSizeString }"
        :class="{top: topSegment}"
        class="segment-col first">
        {{ segment.original }}
      </div>
    </div>
    <div class="br-light-darker ib w-0 h-100p absolute"/>
    <div class="double-block">
      <div
        :class="{top: topSegment}"
        class="segment-col last">
        <textarea
          v-autosize
          ref="ta"
          v-model="segment.translation"
          :min-height="1"
          :style="{ 'font-size': fontSizeString }"
          :disabled="segment.status === 'done'"
          :placeholder="segment.active && segment.status !== 'done' ? 'Sākt tulkot...' : ''"
          rows="1"
          class="segment-edit"
          @focus="() => {$emit('click', segment.id)}"
        />
      </div>
      <div
        :class="{top: topSegment}"
        class="tools-col"
      >
        &nbsp;
      </div>
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
    },
    firstSegmentId: {
      type: Number,
      required: true
    },
    topSegment: {
      type: Boolean,
      required: true
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
    },
    nr: function () {
      return this.segmentData.id - this.firstSegmentId + 1
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
