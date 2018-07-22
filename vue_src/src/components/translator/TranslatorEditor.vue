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
    },
    inactive: {
      type: Boolean,
      default: false
    }
  },
  data: function () {
    return {
      editor: null,
      id: null,
      highlightTagStart: '<span style="background-color: yellow;">',
      highlightTagEnd: '</span>',
      gTagStart: '<span class="bg-blueish">></span>',
      gTagEnd: '<span class="bg-blueish"><</span>'
    }
  },
  computed: {
    formattedText: function () {
      if (this.text === '') {
        return ''
      }
      let result = this.text
      // Process search highlight
      if (this.searchTerm !== '') {
        let termPosition = result.toLowerCase().indexOf(this.searchTerm)
        while (termPosition > -1) {
          result = [result.slice(0, termPosition), this.highlightTagStart, result.slice(termPosition)].join('')
          const end = termPosition + this.searchTerm.length + this.highlightTagStart.length
          result = [result.slice(0, end), this.highlightTagEnd, result.slice(end)].join('')
          termPosition = result.toLowerCase().indexOf(this.searchTerm, end + this.highlightTagEnd.length)
        }
      }
      // Process g tags
      // Find tag start
      let gTagPosition = result.indexOf('&lt;g id="')
      while (gTagPosition > -1) {
        // Find out tag id
        const closingMark = result.indexOf('"', gTagPosition + 10)
        const rawId = result.substring(gTagPosition + 10, closingMark)
        const id = parseInt(rawId)
        result = result.replace('&lt;g id="' + id + '"&gt;', this.gTagStart)
        const endTagPos = result.indexOf('&lt;/g&gt;')
        result = result.replace('&lt;/g&gt;', this.gTagEnd)
        gTagPosition = result.indexOf('&lt;g id="', endTagPos + this.gTagEnd.length)
      }
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
      let result = this.editor.innerHTML.replace(new RegExp(this.highlightTagStart, 'g'), '')
      return result.replace(new RegExp(this.highlightTagEnd, 'g'), '')
    }
  }
}
</script>
