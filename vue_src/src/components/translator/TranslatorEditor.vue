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
      if (this.searchTerm !== '' && this.text !== '') {
        let result = this.text
        let termPosition = result.toLowerCase().indexOf(this.searchTerm)
        while (termPosition > -1) {
          result = [result.slice(0, termPosition), '<span style="background-color: yellow;">', result.slice(termPosition)].join('')
          const end = termPosition + this.searchTerm.length + 40
          result = [result.slice(0, end), '</span>', result.slice(end)].join('')
          termPosition = result.toLowerCase().indexOf(this.searchTerm, end + 47)
        }
        return result
      }
      return this.text
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
          this.editor.contentEditable = true
        })
        return
      }
      if (newVal) {
        this.editor.contentEditable = true
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
      let result = this.editor.innerHTML.replace(new RegExp('<span style="background-color: yellow;">', 'g'), '')
      return result.replace(new RegExp('</span>', 'g'), '')
    }
  }
}
</script>
