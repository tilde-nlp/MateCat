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
            <div class="status column">
              <div
                :class="{'file-new': parseFloat(file.progress) === 0, 'file-draft': parseFloat(file.progress) > 0 && parseFloat(file.progress) < 100, 'file-complete': parseFloat(file.progress) === 100}"
                class="status-circle"
              />
              {{ file.name }}</div>
            <div
              class="additional-row"
            >
              <span v-if="$loading.isLoading('file_' + key)">
                {{ file.loadingStatus === 'UPLOADING' ? 'Augšupielādējas' : 'Analizējas' }}
                <img
                  :src="$assetPath + 'ajax-loader.gif'"
                  class="ib ml-16"
                >
              </span>
              <span v-else>
                <div class="segments column">{{ file.segmentCount }}</div>
                <div class="words column">{{ file.wordCount }}</div>
                <div class="translated column">{{ file.progress }} %</div>
                <div class="created column">{{ file.created }}</div>
                <div class="created-by column">{{ file.owner }}</div>
                <div class="last-modified column">-</div>
                <div class="controls column">
                  <button
                    class="file-list-button"
                    @click="translate(key)"
                  >{{ file.progress > 0 ? 'Rediģēt' : 'Tulkot' }}
                  </button>
                  <span
                    class="icon-span"
                    @click="share(key)"
                  >
                    <svgicon
                      class="svg-icon va-middle"
                      name="share"
                      height="32"
                    />
                  </span>
                  <span
                    class="icon-span"
                    @click="removeFile(key)"
                  >
                    <svgicon
                      class="svg-icon va-middle"
                      name="close"
                      height="32"
                    />
                  </span>
                </div>
              </span>
            </div>
          </div>
        </transition-group>
      </div>
    </div>
    <transition
      name="fade"
      mode="out-in"
    >
      <confirmation
        v-if="showFileDeleteConfirm"
        confirm-text="Dzēst"
        cancel-text="Atcelt"
        @confirm="deleteFile"
        @cancel="cancelFileDelete"
      >
        Vai tiešām vēlaties dzēst failu?
      </confirmation>
    </transition>
  </div>
</template>

<script>
import FileService from '../axios/file'
import LanguageService from '../axios/languages'
import _ from 'lodash'
import Vue from 'vue'
import {Confirmation} from '@shibetec/vue-toolbox'
import {DateConverter} from '../utils/date-converter'
export default {
  name: 'FileList',
  components: {
    'confirmation': Confirmation
  },
  data: function () {
    return {
      uploadFiles: [],
      pendingFiles: [],
      fileUploader: '',
      fileForm: '',
      dragAndDropCapable: false,
      dragActive: false,
      uploadProgress: {},
      getterProgress: {},
      showFileDeleteConfirm: false,
      activeFileDeleteKey: null,
      languages: []
    }
  },
  mounted: function () {
    LanguageService.getList()
      .then(r => {
        this.languages = r.data.languages
      })
    const data = {
      id_team: this.$store.getters.profile.teamId,
      page: 1,
      filter: 0
    }
    FileService.getList(data)
      .then(response => {
        this.uploadFiles = null
        this.uploadFiles = _.map(response.data.data, el => {
          const index = Object.values(this.getterProgress).length
          const link = this.$CONFIG.baseUrl + 'api/v1/jobs/' + el.jobs[0].id + '/' + el.jobs[0].password + '/stats'
          this.getterProgress[index] = {
            projectId: el.id,
            password: el.password,
            index: index,
            link: link
          }
          const data = {
            pid: el.id,
            ppassword: el.password
          }
          FileService.analyze(data)
            .then(this.analyzeResponseForGetter)
          FileService.checkStatus(link)
            .then(this.statusResponseGetter)
          return {
            id: el.id,
            password: el.password,
            jobId: el.jobs[0].id,
            jobPassword: el.jobs[0].password,
            name: el.name,
            wordCount: '...',
            segmentCount: '...',
            owner: el.jobs[0].owner,
            progress: 0,
            loadingStatus: '',
            created: DateConverter.timeStampToDate(el.jobs[0].create_timestamp)
          }
        })
      })
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
          const index = this.uploadFiles.length
          this.uploadProgress[index] = {
            index: index,
            status: 'Augšupielādējas',
            projectId: '',
            password: '',
            fileName: '',
            link: ''
          }
          this.uploadFiles.push({
            name: e.dataTransfer.files[i].name,
            wordCount: 0,
            loadingStatus: 'UPLOADING'
          })
          this.$loading.startLoading('file_' + index)
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
          index: index,
          status: 'Augšupielādējas',
          projectId: '',
          password: '',
          fileName: '',
          link: ''
        }
        this.uploadFiles.push({
          name: this.$refs.fileUploader.files[i].name,
          wordCount: 0
        })
        this.$loading.startLoading('file_' + index)
        this.upload(this.$refs.fileUploader.files[i], index)
      }
    },
    addFiles: function () {
      this.$refs.fileUploader.click()
    },
    removeFile: function (key) {
      this.activeFileDeleteKey = key
      this.showFileDeleteConfirm = false
      this.showFileDeleteConfirm = true
    },
    share: function (key) {
      // this.uploadFiles.splice(key, 1)
    },
    deleteFile: function () {
      if (this.activeFileDeleteKey === null) return
      const data = {
        new_status: 'cancelled',
        res: 'job',
        id: this.uploadFiles[this.activeFileDeleteKey].jobId,
        password: this.uploadFiles[this.activeFileDeleteKey].jobPassword
      }
      FileService.delete(data)
        .then(r => {
          console.log(r)
        })
      this.uploadFiles.splice(this.activeFileDeleteKey, 1)
      this.activeFileDeleteKey = null
      this.showFileDeleteConfirm = false
    },
    cancelFileDelete: function () {
      this.activeFileDeleteKey = null
      this.showFileDeleteConfirm = false
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
    translate: function (key) {
      this.$router.push({name: 'translate', params: {jobId: this.uploadFiles[key].jobId, password: this.uploadFiles[key].jobPassword}})
    },
    upload: function (file, index) {
      // eslint-disable-next-line no-undef
      let formData = new FormData()
      formData.append('files[]', file)
      FileService.upload(formData)
        .then(uploadRes => {
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
          this.uploadProgress[index].projectId = projectRes.data.data.id_project
          this.uploadProgress[index].password = projectRes.data.data.password
          this.uploadProgress[index].link = this.$CONFIG.baseUrl + 'api/v2/projects/' + projectRes.data.data.id_project + '/' + projectRes.data.data.password + '/creation_status'
          this.uploadFiles[index].loadingProgress = 'ANALYZE'
          return FileService.checkStatus(this.uploadProgress[index].link)
        })
        .then(this.statusResponse)
    },
    statusResponse: function (res) {
      if (res.data.status === 202) {
        setTimeout(() => {
          FileService.checkStatus(res.request.responseURL)
            .then(this.statusResponse)
        }, 1000)
      }
      if (res.data.status === 200) {
        const currentUpload = _.find(Object.values(this.uploadProgress), 'link', res.request.responseURL)
        const data = {
          pid: currentUpload.projectId,
          ppassword: currentUpload.password
        }
        FileService.analyze(data)
          .then(this.analyzeResponse)
      }
    },
    analyzeResponse: function (res) {
      let currentUpload
      const array = Object.values(this.uploadProgress)
      const projectId = parseInt(res.data.data.project_id)
      for (let i = 0; i < array.length; i++) {
        if (array[i].projectId === projectId) {
          currentUpload = array[i]
          break
        }
      }
      if (res.data.data.summary.STATUS !== 'DONE') {
        const data = {
          pid: currentUpload.projectId,
          ppassword: currentUpload.password
        }
        setTimeout(() => {
          FileService.analyze(data)
            .then(this.analyzeResponse)
        }, 1000)
      } else {
        this.uploadFiles[currentUpload.index].wordCount = parseInt(res.data.data.summary.TOTAL_RAW_WC)
        this.uploadFiles[currentUpload.index].segmentCount = parseInt(res.data.data.summary.TOTAL_SEGMENTS)
        this.uploadFiles[currentUpload.index].progress = 0.00
        this.uploadFiles[currentUpload.index].created = DateConverter.nowDate()
        this.uploadFiles[currentUpload.index].owner = this.$store.state.profile.email
        this.$loading.endLoading('file_' + currentUpload.index)
        Vue.delete(this.uploadProgress, currentUpload.index)
      }
    },
    analyzeResponseForGetter: function (res) {
      let currentGetter
      const array = Object.values(this.getterProgress)
      const projectId = parseInt(res.data.data.project_id)
      for (let i = 0; i < array.length; i++) {
        if (array[i].projectId === projectId) {
          currentGetter = array[i]
          break
        }
      }
      if (res.data.data.summary.STATUS !== 'DONE') {
        const data = {
          pid: currentGetter.projectId,
          ppassword: currentGetter.password
        }
        setTimeout(() => {
          FileService.analyze(data)
            .then(this.analyzeResponseForGetter)
        }, 1000)
      } else {
        for (let i = 0; i < this.uploadFiles.length; i++) {
          if (this.uploadFiles[i].id === projectId) {
            this.uploadFiles[i].wordCount = parseInt(res.data.data.summary.TOTAL_RAW_WC)
            this.uploadFiles[i].segmentCount = parseInt(res.data.data.summary.TOTAL_SEGMENTS)
            break
          }
        }
      }
    },
    statusResponseGetter: function (res) {
      let currentGetter
      const array = Object.values(this.getterProgress)
      const link = res.request.responseURL
      for (let i = 0; i < array.length; i++) {
        if (array[i].link === link) {
          currentGetter = array[i]
          break
        }
      }
      if (!res.data.stats.ANALYSIS_COMPLETE) {
        setTimeout(() => {
          FileService.checkStatus(link)
            .then(this.statusResponseGetter)
        }, 1000)
      } else {
        for (let i = 0; i < this.uploadFiles.length; i++) {
          if (this.uploadFiles[i].id === currentGetter.projectId) {
            this.uploadFiles[i].progress = parseFloat(res.data.stats.TRANSLATED_PERC).toFixed(2)
            break
          }
        }
      }
    }
  }
}
</script>
