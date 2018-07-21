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
    }
  },
  data: function () {
    return {
      editor: null,
      editorBody: null,
      id: null,
      formattedText: ''
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
    text: function (newVal) {
      this.formattedText = newVal
      if (this.editorBody !== null) {
        this.editorBody.innerHTML = this.formattedText
      }
    }
  },
  mounted: function () {
    this.id = this._uid
  },
  updated: function () {
    this.$nextTick(() => {
      if (this.editor === null) {
        this.editor = document.getElementById('editor-' + this.id).contentDocument
        this.editorBody = this.editor.getElementsByTagName('body')[0]
        let cssLink = document.createElement('link')
        cssLink.href = this.$CONFIG.baseUrl + 'public/vue_dist/main.css'
        console.log(cssLink.href)
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
