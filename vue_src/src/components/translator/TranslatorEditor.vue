<template>
  <div
    :id="'editor-' + id"
    :style="{ 'font-size': fontSizeString }"
    class="editor-container"
    @input="onInput"
    @click.self="focusEditor"
    @mouseup.self="checkSelection"
    v-html="formattedText"
  />
</template>
<script>
import _ from 'lodash'
import {TextHighlighter} from 'utils/text-highlighter'
import {
  xliffToHtml,
  htmlToXliff
} from 'utils/segment/segment-text'
export default {
  name: 'TranslatorEditor',
  props: {
    isActive: {
      type: Boolean,
      default: false
    },
    text: {
      type: String,
      default: ''
    },
    searchTerm: {
      type: String,
      required: true
    },
    inactive: {
      type: Boolean,
      default: false
    },
    focusToggle: {
      type: Boolean,
      default: false
    },
    segmentId: {
      type: Number,
      required: true
    }
  },
  data: function () {
    return {
      editor: null,
      id: null,
      isEditable: false,
      lastValidContent: '',
      autoFocusEditor: false,
      caretPosition: 50,
      w3: false,
      ie: false
    }
  },
  computed: {
    formattedText: function () {
      let result = this.text
      if (this.searchTerm !== '') {
        result = TextHighlighter.add(this.searchTerm, result)
      }
      result = xliffToHtml(result, this.segmentId)
      return result
    },
    fontSizeString: function () {
      return this.$store.state.fontSize + 'px'
    }
  },
  watch: {
    focusToggle: function () {
      if (this.editor === null) {
        this.autoFocusEditor = true
        return
      }
      this.$nextTick(() => {
        this.focusEditor()
      })
    },
    isActive: function (newVal) {
      if (this.editor === null) {
        this.$nextTick(() => {
          this.editor = document.getElementById('editor-' + this.id)
          this.editor.innerHTML = this.formattedText
          this.enableContentEdit()
        })
        this.$nextTick(() => {
          this.focusEditor()
          this.lastValidContent = this.editor.innerHTML
        })
        return
      }
      if (newVal) {
        this.enableContentEdit()
        this.$nextTick(() => {
          this.focusEditor()
          this.lastValidContent = this.editor.innerHTML
        })
      } else {
        this.disableContentEdit()
      }
    },
    formattedText: function (newVal) {
      if (this.editor !== null) {
        this.lastValidContent = newVal
        if (this.isEditable) {
          this.$nextTick(() => {
            this.enableContentEdit()
          })
        }
      }
    }
  },
  mounted: function () {
    this.id = this._uid
    this.$emit('id', this.id)
    this.ie = (typeof document.selection !== 'undefined' && document.selection.type !== 'Control') && true
    this.w3 = (typeof window.getSelection !== 'undefined') && true
  },
  methods: {
    onInput: _.debounce(function () {
      this.caretPosition = this.getCaretPosition()
      this.removeUnwantedTags()
      let cleanText
      try {
        cleanText = this.cleanText()
      } catch (error) {
        this.$Alerts.add(this.$lang.messages.invalid_target_content)
        this.editor.innerHTML = this.lastValidContent
        return
      }
      this.lastValidContent = this.editor.innerHTML
      this.$emit('input', cleanText)
      this.$store.commit('recalculateUnusedTags')
    }, 500),
    removeUnwantedTags: function () {
      const brTag = '<div><br></div>'
      if (this.editor.innerHTML.indexOf(brTag) > -1) {
        this.editor.innerHTML = this.editor.innerHTML.replace(new RegExp(brTag, 'g'), '')
      }
    },
    cleanText: function () {
      const rawText = this.editor.innerHTML
      let result
      result = TextHighlighter.remove(rawText)
      result = htmlToXliff(result, this.segmentId)
      return result
    },
    enableContentEdit: function () {
      if (this.inactive) {
        return
      }
      this.isEditable = true
      for (let i = 0; i < this.editor.childNodes.length; i++) {
        if (this.editor.childNodes[i].className !== 'editor-span') {
          continue
        }
        this.editor.childNodes[i].contentEditable = !this.inactive && true
      }
    },
    disableContentEdit: function () {
      if (this.inactive) {
        return
      }
      this.isEditable = false
      for (let i = 0; i < this.editor.childNodes.length; i++) {
        if (this.editor.childNodes[i].className !== 'editor-span') {
          continue
        }
        this.editor.childNodes[i].contentEditable = false
      }
    },
    focusEditor: function () {
      if (this.editor === null) {
        return
      }
      for (let i = 0; i < this.editor.childNodes.length; i++) {
        if (this.editor.childNodes[i].className !== 'editor-span') {
          continue
        }
        this.editor.childNodes[i].focus()
        break
      }
      this.caretPosition = this.getCaretPosition()
    },
    checkSelection: function () {
      const selectedText = this.getSelectionText()
      if (selectedText === '') {
        return
      }
      this.$emit('termSearch', selectedText)
    },
    insertTextAtCaret: function (text) {
      // eslint-disable-next-line no-undef
      insertHtmlAtCaret(text)
      this.$nextTick(() => {
        this.$nextTick(() => {
          this.removeUnwantedTags()
          let cleanText
          try {
            cleanText = this.cleanText()
          } catch (error) {
            this.$Alerts.add(this.$lang.messages.invalid_target_content)
            this.editor.innerHTML = this.lastValidContent
            return
          }
          this.lastValidContent = this.editor.innerHTML
          this.$emit('input', cleanText)
          this.$store.commit('recalculateUnusedTags', 'dud')
        })
      })
    },
    getSelectionText: function () {
      if (window.getSelection) {
        return window.getSelection().toString()
      } else if (document.selection && document.selection.type !== 'Control') {
        return document.selection.createRange().text
      }
      return ''
    },
    getCaretPosition: function () {
      const element = document.getElementById('editor-' + this.id)
      try {
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
      } catch (e) {
        return 0
      }
      return 0
    }
  }
}
</script>
