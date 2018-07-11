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
        <span
          v-if="!segment.active || !splitActive">{{ originalProcessed }}</span>
        <textarea
          v-autosize
          v-show="segment.active && splitActive"
          ref="oa"
          v-model="originalProcessed"
          :min-height="1"
          :style="{ 'font-size': fontSizeString }"
          rows="1"
          class="segment-edit split-edit"
          @click="setSplit"
        />
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
          :placeholder="segment.active && segment.status !== 'done' ? $lang.inputs.start_translating : ''"
          rows="1"
          class="segment-edit"
          @focus="() => {$emit('click', segment.id)}"
          @input="onSegmentInput"
        />
      </div>
      <div
        v-if="segment.status === 'done'"
        :class="toolsType"
        class="tools-col"
      >
        <div
          v-if="segment.saveType === 'MT'"
          class="ma"
        >MT</div>
        <div
          v-if="segment.saveType === 'TM'"
          class="ma"
        >{{ segment.match }}</div>
        <svgicon
          v-if="segment.saveType === 'MANUAL'"
          class="svg-icon static ma icon-blueish-darker-still"
          name="pencil"
          height="24"
        />
      </div>
      <div
        v-else
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
    },
    splitActive: {
      type: Boolean,
      required: true
    }
  },
  data: function () {
    return {
      segment: {},
      ta: '',
      oa: '',
      splitSpacer: '##$_SPLIT$##',
      splitChar: ' & '
    }
  },
  computed: {
    fontSizeString: function () {
      return this.fontSize + 'px'
    },
    nr: function () {
      return this.segmentData.id - this.firstSegmentId + 1
    },
    toolsType: function () {
      return {
        top: this.topSegment,
        'type-mt': this.segment.saveType === 'MT',
        'type-high': this.segment.saveType === 'TM' && this.segment.match > 69,
        'type-mid': this.segment.saveType === 'TM' && this.segment.match < 70 && this.segment.match > 49,
        'type-low': this.segment.saveType === 'TM' && this.segment.match < 50,
        'type-manual': this.segment.saveType === 'MANUAL'
      }
    },
    originalProcessed: function () {
      if (typeof (this.segment.original) === 'undefined') {
        return
      }
      let newString = this.segment.original
      while (1) {
        newString = newString.replace(this.splitSpacer, this.splitChar)
        if (newString.indexOf(this.splitSpacer) < 0) {
          break
        }
      }
      return newString
    }
  },
  mounted: function () {
    this.segment = this.segmentData
  },
  methods: {
    copySourceToTarget: function () {
      this.segment.translation = this.segment.original
    },
    onSegmentInput: function () {
      if (this.segment.saveType === 'MANUAL') {
        return
      }
      this.segment.saveType = 'MANUAL'
      this.segment.match = 0
    },
    setSplit: function () {
      const cursorPosition = this.$refs.oa.selectionStart
      this.segment.original = [
        this.segment.original.slice(0, cursorPosition),
        this.splitSpacer,
        this.segment.original.slice(cursorPosition)
      ].join('')
      this.$refs.oa.blur()
    }
  }
}
</script>
