<template>
  <div class="translator-editor-container">
    <iframe
      :id="'editor-' + id"
      frameborder="0"
      width="100%"
    />
  </div>
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
    fontSize: {
      type: String,
      required: true
    },
    searchTerm: {
      type: String,
      required: true
    }
  },
  data: function () {
    return {
      editor: null,
      editorBody: null,
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
    }
  },
  watch: {
    isActive: function (newVal) {
      if (this.editor === null) return
      if (newVal) {
        this.editor.designMode = 'On'
      } else {
        this.editor.designMode = 'Off'
      }
    },
    formattedText: function (newVal) {
      if (this.editorBody !== null) {
        this.editorBody.innerHTML = newVal
      }
    }
  },
  mounted: function () {
    this.id = this._uid
  },
  contentUpdate: function (e) {
    console.log(e)
  },
  updated: function () {
    this.$nextTick(() => {
      if (this.editor === null) {
        this.editor = document.getElementById('editor-' + this.id).contentDocument
        this.editor.addEventListener('keyup', _.debounce(() => {
          // Todo Remove tags
          this.$emit('input', this.editorBody.innerHTML)
        }, 500), false)
        this.editorBody = this.editor.getElementsByTagName('body')[0]
        let cssLink = document.createElement('link')
        cssLink.href = this.$CONFIG.baseUrl + 'public/vue_dist/main.css'
        cssLink.rel = 'stylesheet'
        cssLink.type = 'text/css'
        this.editor.getElementsByTagName('head')[0].appendChild(cssLink)
        this.editorBody.className = 'editor-container'
        this.editorBody.style.fontSize = this.fontSize
        this.editorBody.innerHTML = this.formattedText
      }
      if (this.isActive) {
        this.editor.designMode = 'On'
      } else {
        this.editor.designMode = 'Off'
      }
    })
  }
}
</script>
