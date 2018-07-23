<template>
  <div
    :id="'editor-' + id"
    :style="{ 'font-size': fontSizeString }"
    class="editor-container"
    @input="onInput"
    v-html="formattedText"
  />
</template>
<script>
import _ from 'lodash'
import {TextHighlighter} from 'utils/text-highlighter'
import {TagsConverter} from 'utils/tags-converter'
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
    }
  },
  data: function () {
    return {
      editor: null,
      id: null
    }
  },
  computed: {
    formattedText: function () {
      if (this.text === '') {
        return ''
      }
      let result = this.text
      if (this.searchTerm !== '') {
        result = TextHighlighter.add(this.searchTerm, result)
      }
      result = TagsConverter.add(result)
      return result
    },
    fontSizeString: function () {
      return this.$store.state.fontSize + 'px'
    }
  },
  watch: {
    isActive: function (newVal) {
      if (this.editor === null) {
        this.$nextTick(() => {
          this.editor = document.getElementById('editor-' + this.id)
          this.editor.innerHTML = this.formattedText
          this.editor.contentEditable = !this.inactive && true
          this.editor.focus()
        })
        return
      }
      if (newVal) {
        this.editor.contentEditable = !this.inactive && true
      } else {
        this.editor.contentEditable = false
      }
    },
    formattedText: function (newVal) {
      if (this.editor !== null) {
        this.editor.innerHTML = newVal
      }
    }
  },
  mounted: function () {
    this.id = this._uid
  },
  methods: {
    onInput: _.debounce(function () {
      this.$emit('input', this.cleanText())
    }, 500),
    cleanText: function () {
      let result = this.editor.innerHTML
      result = TextHighlighter.remove(result)
      result = TagsConverter.remove(result)
      return result
    }
  }
}
</script>
