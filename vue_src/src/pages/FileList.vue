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
      >Velciet failu šeit
      </div>
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
            <div
              v-if="$loading.isLoading('file_' + key)"
              class="ib"
            >
              Augšupielādējas
              <img
                :src="$assetPath + 'ajax-loader.gif'"
                class="ib ml-16"
              >
            </div>
            <div
              v-else
              class="ib"
            >
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
                >Rediģēt
                </button>
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
          </div>
        </transition-group>
      </div>
    </div>
  </div>
</template>

<script>
import FileService from '../axios/file'
import _ from 'lodash'

export default {
  name: 'FileList',
  data: function () {
    return {
      uploadFiles: [],
      pendingFiles: [],
      fileUploader: '',
      fileForm: '',
      dragAndDropCapable: false,
      dragActive: false,
      uploadProgress: {}
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
          this.upload(e.dataTransfer.files[i])
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
      for (let i = 0; i < this.$refs.fileUploader.files.length; i++) {
        const index = this.uploadFiles.length
        this.uploadProgress[index] = {
          status: 'Augšupielādējas',
          projectId: '',
          password: '',
          fileName: '',
          link: ''
        }
        this.uploadFiles.push(this.$refs.fileUploader.files[i])
        this.$loading.startLoading('file_' + index)
        this.upload(this.$refs.fileUploader.files[i], index)
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
      let div = document.createElement('div')

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
    },
    upload: function (file, index) {
      // eslint-disable-next-line no-undef
      let formData = new FormData()
      formData.append('files[]', file)
      FileService.upload(formData)
        .then(uploadRes => {
          console.log('upload response')
          console.log(uploadRes)
          this.uploadProgress[index].fileName = uploadRes.data[0].name
          // eslint-disable-next-line no-undef
          let convertFormData = new FormData()
          convertFormData.append('action', 'convertFile')
          convertFormData.append('file_name', this.uploadProgress[index].fileName)
          convertFormData.append('source_lang', 'en-US')
          convertFormData.append('target_lang', 'fr-FR')
          convertFormData.append('segmentation_rule', '')
          return FileService.convert(convertFormData)
        })
        .then(convertRes => {
          console.log('convert response')
          console.log(convertRes.data)
          // eslint-disable-next-line no-undef
          let projectFormData = new FormData()
          projectFormData.append('action', 'createProject')
          projectFormData.append('project_name', '')
          projectFormData.append('file_name', this.uploadProgress[index].fileName)
          projectFormData.append('source_language', 'en-US')
          projectFormData.append('target_language', 'fr-FR')
          projectFormData.append('job_subject', 'general')
          projectFormData.append('disable_tms_engine', 'false')
          projectFormData.append('mt_engine', '1')
          projectFormData.append('private_key_list', '{"ownergroup":[],"mine":[],"anonymous":[]}')
          projectFormData.append('lang_detect_files[' + this.uploadProgress[index].fileName + ']', 'detect')
          projectFormData.append('pretranslate_100', '0')
          projectFormData.append('lexiqa', 'false')
          projectFormData.append('speech2text', 'false')
          projectFormData.append('tag_projection', 'true')
          projectFormData.append('segmentation_rule', '')
          projectFormData.append('dqf', 'false')
          projectFormData.append('get_public_matches', 'true')
          return FileService.createProject(projectFormData)
        })
        .then(projectRes => {
          console.log('project response')
          console.log(projectRes.data)
          this.uploadProgress[index].projectId = projectRes.data.data.id_project
          this.uploadProgress[index].password = projectRes.data.data.password
          this.uploadProgress[index].link = this.$CONFIG.baseUrl + 'api/v2/projects/' + projectRes.data.data.id_project + '/' + projectRes.data.data.password + '/creation_status'
          return FileService.checkStatus(this.uploadProgress[index].link)
        })
        .then(this.statusResponse)
    },
    statusResponse: function (res) {
      console.log('status response')
      console.log(res)
      if (res.data.status === 202) {
        setTimeout(() => {
          FileService.checkStatus(res.request.responseURL)
            .then(this.statusResponse)
        }, 1000)
      }
      if (res.data.status === 200) {
        console.log('Project created')
        const currentUpload = _.find(this.uploadProgress, 'link', res.request.responseURL)
        // eslint-disable-next-line no-undef
        let formData = new FormData()
        formData.append('pid', currentUpload.projectId)
        formData.append('ppassword', currentUpload.password)
        FileService.analyze(formData)
          .then(analyzeRes => {
            console.log('Analyze complete')
            console.log(analyzeRes)
          })
      }
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
