<template>
  <div
    :class="{active: segment.active, confirmed: segment.status === 'done'}"
    class="segment-container font-size-0"
    @click.capture="setActive"
    @click.self="focusEditor"
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
          @termSearch="text => { $emit('termSearch', text) }"
        />
      </div>
    </div>
    <div class="br-light-darker ib w-0 h-100p absolute"/>
    <div
      class="double-block"
      @click.self="focusEditor"
    >
      <div
        v-shortkey="['ctrl', 'a']"
        :class="{top: topSegment}"
        :style="rowMinHeight"
        :lang="targetLang"
        class="segment-col last"
        spellcheck="true"
        @click.self="focusEditor"
        @shortkey="autocompleteText()"
      >
        <translator-editor
          v-if="segmentData.id > 0 && segmentData.id !== null"
          ref="targetEditor"
          :is-active="isActive"
          :text="segment.translation"
          :search-term="$store.state.targetSearch"
          :focus-toggle="segment.focusToggle"
          :segment-id="segmentData.id"
          :contenteditable="isActive"
          @input="onSegmentInput"
          @id="id => { editorId = id }"
        />
        <div
          v-shortkey="['arrowdown']"
          v-if="isActive && autocompleteSuggestions.length"
          class="autocomplete-suggestions"
          @shortkey="nextSuggestion()"
        >
          <div
            v-for="(acText, index) in autocompleteSuggestions"
            :key="index + 1"
            :class="{active: index === activeSuggestion}"
            class="autocomplete-suggestion"
          >
            {{ acText.pre }}<b>{{ acText.post }}</b>
          </div>
        </div>
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
              @click="insertTag(tag)"
              @shortkey="insertTag(tag)"
            >
              {{ tag.name }}
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
import _ from 'lodash'
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
      editorId: '',
      autocompleteSuggestions: [],
      activeSuggestion: -1,
      w3: false,
      ie: false
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
    this.ie = (typeof document.selection !== 'undefined' && document.selection.type !== 'Control') && true
    this.w3 = (typeof window.getSelection !== 'undefined') && true
  },
  methods: {
    copySourceToTarget: function () {
      this.segment.translation = this.segment.original
    },
    onSegmentInput: function (val) {
      this.showAutocomplete(val)
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
      this.$refs.targetEditor.insertTextAtCaret(tag.toFullHtml())
    },
    focusEditor: function () {
      this.segment.focusToggle = !this.segment.focusToggle
    },
    showAutocomplete: function (value) {
      const entered = this.getLastWord(value)
      this.autocompleteSuggestions = this.findSuggestions(entered)
    },
    getLastWord: function (value) {
      if (typeof (value) === 'undefined' || value === null) {
        return ''
      }
      const caretPosition = this.getCaretPosition()
      const words = value.substring(0, caretPosition).split(' ')
      return words[words.length - 1]
    },
    findSuggestions: function (entered) {
      if (typeof (this.segmentData.topSuggestion) === 'undefined' && this.segmentData.topSuggestion === '') {
        return []
      }
      const suggestionWords = this.segmentData.topSuggestion.split(' ')
      const suggestions = _.filter(suggestionWords, el => {
        return el.startsWith(entered)
      })
      let finalSuggestions = []
      for (let i = 0; i < suggestions.length; i++) {
        finalSuggestions.push({pre: entered, post: suggestions[i].substring(entered.length)})
      }
      if (finalSuggestions[0]) {
        this.activeSuggestion = 0
      } else {
        this.activeSuggestion = -1
      }
      return finalSuggestions
    },
    autocompleteText: function () {
      if (this.autocompleteSuggestions.length < 1) {
        return
      }
      const insertText = this.autocompleteSuggestions[this.activeSuggestion].post
      this.insertTextAtCursor(insertText)
      this.autocompleteSuggestions = []
      this.activeSuggestion = -1
    },
    insertTextAtCursor: function (text) {
      let sel, range
      sel = window.getSelection()
      range = sel.getRangeAt(0)
      range.deleteContents()
      let textNode = document.createTextNode(text)
      range.insertNode(textNode)
      range.setStartAfter(textNode)
      sel.removeAllRanges()
      sel.addRange(range)
    },
    getCaretPosition: function () {
      const element = document.getElementById('editor-' + this.editorId)
      if (this.w3) {
        let range = window.getSelection().getRangeAt(0)
        let preCaretRange = range.cloneRange()
        preCaretRange.selectNodeContents(element)
        preCaretRange.setEnd(range.endContainer, range.endOffset)
        return preCaretRange.toString().length
      }
      if (this.ie) {
        let textRange = document.selection.createRange()
        let preCaretTextRange = document.body.createTextRange()
        preCaretTextRange.moveToElementText(element)
        preCaretTextRange.setEndPoint('EndToEnd', textRange)
        return preCaretTextRange.text.length
      }
      return 0
    },
    nextSuggestion: function () {
      this.activeSuggestion++
      if (this.activeSuggestion === this.autocompleteSuggestions.length) {
        this.activeSuggestion = 0
      }
    }
  }
}
</script>
