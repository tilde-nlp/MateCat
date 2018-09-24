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
      autoFocusEditor: false
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
  },
  methods: {
    onInput: _.debounce(function () {
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
      this.editor.focus()
    },
    checkSelection: function () {
      const selectedText = this.getSelectionText()
      if (selectedText === '') {
        return
      }
      this.$emit('termSearch', selectedText)
    },
    getSelectionText: function () {
      if (window.getSelection) {
        return window.getSelection().toString()
      } else if (document.selection && document.selection.type !== 'Control') {
        return document.selection.createRange().text
      }
      return ''
    }
  }
}
</script>
