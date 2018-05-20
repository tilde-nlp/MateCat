<template>
  <div class="page-container">
    <form
      ref="fileForm"
      :class="{active: dragActive}"
      class="file-dropoff"
    >
      <span class="vam-helper"/>
      <button
        class="file-upload-button"
        @click="addFiles"
      >Izvēlēties failus
      </button>
      <input
        id="fileUploader"
        ref="fileUploader"
        type="file"
        multiple
        @change="handleFileUpload"
      >
      <div
        v-if="dragAndDropCapable"
        class="file-dropoff-note"
      >Velciet failu šeit</div>
    </form>
    <div class="file-list-container">
      <div class="file-list-header">
        <div class="status">Statuss</div>
        <div class="segments">Segmenti</div>
        <div class="words">Vārdi</div>
        <div class="translated">Iztulkots</div>
        <div class="created">Ielādes datums</div>
        <div class="created-by">Izveidoja</div>
        <div class="last-modified">Pēdējās izmaiņas</div>
        <div class="controls">&nbsp;</div>
      </div>
      <div class="file-list">
        <transition-group
          name="fade"
          mode="out-in">
          <div
            v-for="(file, key) in uploadFiles"
            :key="key"
            class="file-row"
          >
            <div class="status column">{{ file.name }}</div>
            <div class="segments column">120</div>
            <div class="words column">520</div>
            <div class="translated column">55%</div>
            <div class="created column">22.02.2018</div>
            <div class="created-by column">Jānis Bērziņš</div>
            <div class="last-modified column">18.05.2018</div>
            <div class="controls column">
              <button
                class="file-list-button"
                @click="translate"
              >Rediģēt</button>
              <svgicon
                class="svg-icon"
                name="share"
                height="30"
              />
              <span @click="removeFile(key)">
                <svgicon
                  class="svg-icon"
                  name="close"
                  height="30"
                />
              </span>
            </div>
          </div>
        </transition-group>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FileList',
  data: function () {
    return {
      uploadFiles: [],
      fileUploader: '',
      fileForm: '',
      dragAndDropCapable: false,
      dragActive: false
    }
  },
  mounted: function () {
    /*
      Determine if drag and drop functionality is capable in the browser
    */
    this.dragAndDropCapable = this.determineDragAndDropCapable()

    /*
      If drag and drop capable, then we continue to bind events to our elements.
    */
    if (this.dragAndDropCapable) {
      /*
        Listen to all of the drag events and bind an event listener to each
        for the fileform.
      */
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

      /*
        Add an event listener for drop to the form
      */
      this.$refs.fileForm.addEventListener('drop', e => {
        this.dragActive = false
        /*
          Capture the files from the drop event and add them to our local files
          array.
        */
        for (let i = 0; i < e.dataTransfer.files.length; i++) {
          this.uploadFiles.push(e.dataTransfer.files[i])
        }
      })
      /*
        Add form active state when file is hovered.
      */
      this.$refs.fileForm.addEventListener('dragover', e => {
        this.dragActive = true
      })
      /*
        Remove form active state when drag left.
      */
      this.$refs.fileForm.addEventListener('dragleave', e => {
        this.dragActive = false
      })
    }
  },
  methods: {
    handleFileUpload: function () {
      console.log(this.$refs.fileUploader.files)
      for (let i = 0; i < this.$refs.fileUploader.files.length; i++) {
        this.uploadFiles.push(this.$refs.fileUploader.files[i])
      }
    },
    addFiles: function () {
      this.$refs.fileUploader.click()
    },
    removeFile: function (key) {
      this.uploadFiles.splice(key, 1)
    },
    /*
      Determines if the drag and drop functionality is in the
      window
    */
    determineDragAndDropCapable: function () {
      /*
        Create a test element to see if certain events
        are present that let us do drag and drop.
      */
      var div = document.createElement('div')

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
    translate: function () {
      this.$router.push({name: 'translate'})
    }
  }
}
</script>

<style lang="less" scoped>
  @import (reference) "~less-entry";

  #fileUploader {
    display: none;
  }

  .file-dropoff {
    transition: all unit(@golden / 10, s) ease-in-out;
    border: solid 1px @color-grey-darker;
    &.active {
      border: solid 1px @color-red;
    }
    height: @spacer-64;
    .file-upload-button {
      .mr-32;
      vertical-align: middle;
      width: @spacer-128;
    }
    .file-dropoff-note {
      display: inline-block;
      vertical-align: middle;
      .size-m;
    }
  }

  .file-list-container {
    .mt-16;
    .size-s;
    text-align: left;
    .status {
      width: @spacer-192;
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
    }
    .segments {
      width: 74px;
    }
    .words {
      width: 74px;
    }
    .translated {
      width: 74px;
    }
    .created {
      width: 160px;
    }
    .created-by {
      width: 220px;
    }
    .last-modified {
      width: 160px;
    }
    .controls {
      width: 200px;
    }
    .file-list-header {
      .mb-8;
      div {
        display: inline-block;
      }
    }
    .file-list {
      .file-row {
        display: block;
        border-bottom: solid 1px @color-light-darker;
        .pb-8;
        .column {
          display: inline-block;
        }
      }
    }
    .file-list-button {
      .w-128;
      display: inline-block;
    }
  }
</style>
