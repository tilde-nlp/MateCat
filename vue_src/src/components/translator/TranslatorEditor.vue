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
      id: null,
      isEditable: false
    }
  },
  computed: {
    formattedText: function () {
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
          this.enableContentEdit()
        })
        this.$nextTick(() => {
          console.log('focus element 1')
          this.focusEditor()
        })
        return
      }
      if (newVal) {
        this.enableContentEdit()
        this.$nextTick(() => {
          console.log('focus element 2')
          this.focusEditor()
        })
      } else {
        this.disableContentEdit()
      }
    },
    formattedText: function (newVal) {
      if (this.editor !== null) {
        this.editor.innerHTML = newVal
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
        if (this.editor.childNodes[i].className === 'editor-span') {
          this.editor.childNodes[i].focus()
          break
        }
      }
    }
  }
}
</script>
