<template>
  <div
    :class="{active: segment.active, confirmed: segment.status === 'done'}"
    class="segment-container font-size-0"
    @click.capture="setActive"
  >
    <div class="double-block segment-fix">
      <div
        :class="{top: topSegment}"
        class="number-col bl-light-darker">
        <div class="ma">{{ nr }}</div>
      </div>
      <div
        :class="{top: topSegment}"
        class="segment-col first">
        <translator-editor
          :is-active="isActive"
          :text="originalProcessed"
          :search-term="$store.state.sourceSearch"
          :inactive="true"
        />
      </div>
    </div>
    <div class="br-light-darker ib w-0 h-100p absolute"/>
    <div class="double-block">
      <!--<div class="tag-insert-container">-->
      <!--<div-->
      <!--v-for="(tag, index) in unusedTags"-->
      <!--:key="index"-->
      <!--class="tag-insert"-->
      <!--&gt;-->
      <!--{{ tag.id }}-->
      <!--</div>-->
      <!--</div>-->
      <div
        :class="{top: topSegment}"
        :style="rowMinHeight"
        class="segment-col last"
      >
        <translator-editor
          :is-active="isActive"
          :text="segment.translation"
          :search-term="$store.state.targetSearch"
          :focus-toggle="segment.focusToggle"
          @input="onSegmentInput"
        />
      </div>
      <div
        :class="toolsType"
        class="tools-col"
      >
        <div
          v-if="segment.saveType === 'MT'"
          class="va-top mt-4"
        >MT</div>
        <div
          v-if="segment.saveType === 'TM'"
          class="va-top mt-4"
        >{{ segment.match }}%</div>
        <svgicon
          v-if="segment.saveType === 'MANUAL'"
          class="svg-icon static va-top mt-4 icon-blueish-darker-still"
          name="pencil"
          height="24"
        />
        <svgicon
          v-if="segment.comments && segment.comments.length > 0"
          class="svg-icon static icon-orange chat-icon"
          name="chat-baloon"
          height="24"
        />
      </div>
    </div>
  </div>
</template>
<script>
import TranslatorEditor from 'components/translator/TranslatorEditor'
import {TagsConverter} from 'utils/tags-converter'
export default {
  name: 'TranslatorSegment',
  components: {
    'translator-editor': TranslatorEditor
  },
  props: {
    segmentData: {
      type: Object,
      required: true
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
    nr: function () {
      return this.segmentData.id - this.firstSegmentId + 1
    },
    rowMinHeight: function () {
      if (this.segment.comments && this.segment.comments.length > 0 &&
        (this.segment.saveType === 'MT' || this.segment.saveType === 'TM' || this.segment.saveType === 'MANUAL')) {
        return {'min-height': '60px'}
      } else {
        return {'min-height': '30px'}
      }
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
    },
    isActive: function () {
      return this.segment.active
    },
    unusedTags: function () {
      if (this.$store.state.activeSegment === null) {
        return []
      }
      const originalTags = TagsConverter.getTagList(this.$store.state.activeSegment.original)
      return originalTags
      // const translationTags = TagsConverter.getTagList(this.$store.state.activeSegment.translation)
      // return []
    }
  },
  watch: {
    isActive: function (newVal) {
      if (newVal) {
        // this.$refs.ta.focus()
      }
    }
  },
  mounted: function () {
    this.segment = this.segmentData
  },
  methods: {
    copySourceToTarget: function () {
      this.segment.translation = this.segment.original
    },
    onSegmentInput: function (val) {
      this.segment.cleanTranslation = val
      this.$emit('inputDebounce')
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
    },
    setActive: function () {
      this.$emit('click', this.segment.id)
    }
  }
}
</script>
