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
          v-if="segmentData.id > 0 && segmentData.id !== null"
          :is-active="isActive"
          :text="originalProcessed"
          :search-term="$store.state.sourceSearch"
          :inactive="true"
          :segment-id="segmentData.id"
        />
      </div>
    </div>
    <div class="br-light-darker ib w-0 h-100p absolute"/>
    <div
      class="double-block"
      @click.self="focusEditor"
    >
      <div
        :class="{top: topSegment}"
        :style="rowMinHeight"
        :lang="targetLang"
        class="segment-col last"
        spellcheck="true"
        @click.self="focusEditor"
      >
        <translator-editor
          v-if="segmentData.id > 0 && segmentData.id !== null"
          :is-active="isActive"
          :text="segment.translation"
          :search-term="$store.state.targetSearch"
          :focus-toggle="segment.focusToggle"
          :segment-id="segmentData.id"
          @input="onSegmentInput"
          @id="id => { editorId = id }"
        />
        <div
          v-if="showTags"
          class="tag-insert-container"
        >
          <transition-group
            name="ffade"
            mode="out-in">
            <div
              v-shortkey="['ctrl', 'alt', parseInt(index + 1)]"
              v-for="(tag, index) in $store.state.unusedTags"
              :key="index"
              class="tag-insert"
              @shortkey="insertTag(tag)"
            >
              {{ tag.id }}
            </div>
          </transition-group>
        </div>
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
    },
    targetLang: {
      type: String,
      required: true
    }
  },
  data: function () {
    return {
      segment: {},
      ta: '',
      oa: '',
      splitSpacer: '##$_SPLIT$##',
      splitChar: ' & ',
      editorId: ''
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
    showTags: function () {
      return this.$store.state.activeSegment !== null &&
        this.$store.state.activeSegment.id === this.segmentData.id
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
    },
    insertTag: function (tag) {
      const tagString = TagsConverter.getEmptyTag(tag, 'editor-' + this.editorId)
      this.inserTagHtml(tagString, false)
    },
    focusEditor: function () {
      this.segment.focusToggle = !this.segment.focusToggle
    },
    inserTagHtml: function (html, selectPastedContent) {
      let sel
      let range
      if (window.getSelection) {
        // IE9 and non-IE
        sel = window.getSelection()
        if (sel.getRangeAt && sel.rangeCount) {
          range = sel.getRangeAt(0)
          range.deleteContents()

          // Range.createContextualFragment() would be useful here but is
          // only relatively recently standardized and is not supported in
          // some browsers (IE9, for one)
          let el = document.createElement('div')
          el.innerHTML = html
          let frag = document.createDocumentFragment()
          let node
          let lastNode
          node = el.firstChild
          while (node) {
            lastNode = frag.appendChild(node)
            node = el.firstChild
          }
          let firstNode = frag.firstChild
          range.insertNode(frag)

          // Preserve the selection
          if (lastNode) {
            range = range.cloneRange()
            range.setStartAfter(lastNode)
            if (selectPastedContent) {
              range.setStartBefore(firstNode)
            } else {
              range.collapse(true)
            }
            sel.removeAllRanges()
            sel.addRange(range)
          }
        }
      } else if ((sel = document.selection) && sel.type !== 'Control') {
        // IE < 9
        let originalRange = sel.createRange()
        originalRange.collapse(true)
        sel.createRange().pasteHTML(html)
        let range = sel.createRange()
        range.setEndPoint('StartToStart', originalRange)
        range.select()
      }
    }
  }
}
</script>
