<template>
  <div class="file-selector-container">
    <form
      ref="fileForm"
      :class="{active: dragActive}"
      class="file-dropoff"
      @click="addFiles"
    >
      <span class="vam-helper"/>
      <input
        id="fileUploader"
        ref="fileUploader"
        type="file"
        multiple
        @change="handleFileUpload"
      >
      <svgicon
        class="svg-icon va-middle"
        name="add-file"
        height="30"
      />
      <div
        v-if="dragAndDropCapable"
        class="file-dropoff-note"
      >Augšupielādēt vai ievilkt failu
      </div>
    </form>
  </div>
</template>

<script>
import _ from 'lodash'
export default {
  name: 'FileListSelector',
  data: function () {
    return {
      fileForm: '',
      dragActive: false,
      fileUploader: '',
      dragAndDropCapable: false
    }
  },
  mounted: function () {
    this.dragAndDropCapable = this.determineDragAndDropCapable()
    if (this.dragAndDropCapable) {
      // Listen to all of the drag events and bind an event listener to each for the file form.
      ['drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop'].forEach(evt => {
        /*
          For each event add an event listener that prevents the default action
          (opening the file in the browser) and stop the propagation of the event (so
          no other elements open the file in the browser)
        */
        this.$refs.fileForm.addEventListener(evt, (e) => {
          e.preventDefault()
          e.stopPropagation()
        }, false)
      })
      // Add an event listener for drop to the form.
      this.$refs.fileForm.addEventListener('drop', e => {
        this.dragActive = false
        // Capture the files from the drop event and process them.
        _.forEach(e.dataTransfer.files, f => {
          this.$emit('fileAdded', f)
        })
      })
      // Add form active state when file is hovered.
      this.$refs.fileForm.addEventListener('dragover', () => {
        this.dragActive = true
      })
      // Remove form active state when drag left.
      this.$refs.fileForm.addEventListener('dragleave', () => {
        this.dragActive = false
      })
    }
  },
  methods: {
    determineDragAndDropCapable: function () {
      // Create a test element to see if certain events are present that let us do drag and drop.
      const div = document.createElement('div')
      /*
        Check to see if the `draggable` event is in the element
        or the `ondragstart` and `ondrop` events are in the element. If
        they are, then we have what we need for dragging and dropping files.
        We also check to see if the window has `FormData` and `FileReader` objects
        present so we can do our AJAX uploading
      */
      return (('draggable' in div) ||
        ('ondragstart' in div && 'ondrop' in div)) &&
        'FormData' in window &&
        'FileReader' in window
    },
    addFiles: function () {
      this.$refs.fileUploader.click()
    },
    handleFileUpload: function () {
      _.forEach(this.$refs.fileUploader.files, f => {
        this.$emit('fileAdded', f)
      })
    }
  }
}
</script>
