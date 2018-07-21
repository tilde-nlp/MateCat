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
    }
  },
  data: function () {
    return {
      editor: '',
      id: null,
      formattedText: ''
    }
  },
  watch: {
    isActive: function (newVal) {
      if (document.getElementById('editor-' + this.id) === null) return
      if (newVal) {
        document.getElementById('editor-' + this.id).contentDocument.designMode = 'On'
      } else {
        document.getElementById('editor-' + this.id).contentDocument.designMode = 'Off'
      }
    },
    text: function (newVal) {
      this.formattedText = newVal
    }
  },
  mounted: function () {
    this.id = this._uid
  },
  updated: function () {
    this.$nextTick(() => {
      if (this.isActive) {
        document.getElementById('editor-' + this.id).contentDocument.designMode = 'On'
      } else {
        document.getElementById('editor-' + this.id).contentDocument.designMode = 'Off'
      }
    })
  }
}
</script>
