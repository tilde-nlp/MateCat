<template>
  <div
    :id="'editor-' + id"
    class="editor-container"
    v-html="formattedText"
  />
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
      if (this.editor === null) {
        this.$nextTick(() => {
          this.editor = document.getElementById('editor-' + this.id)
          // this.editor.addEventListener('keyup', _.debounce(() => {
          //   // Todo Remove tags
          //   this.$emit('input', this.editorBody.innerHTML)
          // }, 500), false)
          this.editor.style.fontSize = this.fontSize
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
      if (this.editorBody !== null) {
        this.editorBody.innerHTML = newVal
      }
    }
  },
  mounted: function () {
    this.id = this._uid
  }
}
</script>
