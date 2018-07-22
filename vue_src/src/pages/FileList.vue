<template>
  <div class="page-container">
    <div class="section-bg bg-grey-light">
      <div class="bb-blueish"/>
      <section class="section">
        <!-- FILE UPLOAD TOGGLE -->
        <div
          class="head-control"
        >
          <svgicon
            class="svg-icon va-middle"
            name="file"
            height="24"
          />
          <div class="link ib">{{ $lang.buttons.add_files }}</div>
          <svgicon
            :class="{open: sliderOpen}"
            class="svg-icon va-middle chevron"
            name="chevron"
            height="24"
          />
        </div>
        <!-- FILE UPLOAD TOGGLE END -->
      </section>
      <div class="bb-blueish"/>
      <section class="section">
        <!-- FILE UPLOAD CONTAINER -->
        <div
          :class="{open: sliderOpen}"
          class="slider-container"
        >
          <file-list-toolbar
            :button-enabled="uploadQueue.length > 0"
            @fromLangChange="value => { fromLang = value }"
            @toLangChange="value => { toLang = value }"
            @subjectChange="value => { subject = value }"
            @translate="nextFileUpload"
            @toggleSettings="() => { settingsOpen = !settingsOpen }"
          />
          <file-list-selector
            @fileAdded="sendToUploadQueue"
          />
        </div>
        <!-- FILE UPLOAD CONTAINER END -->
        <!-- FILE QUEUE -->
        <transition
          name="ffade"
          mdde="out-in">
          <div
            v-if="uploadQueue.length"
            class="file-queue-container"
          >
            <transition-group
              name="ffade"
              mode="out-in">
              <div
                v-for="(file, index) in uploadQueue"
                :key="index"
                class="">
                <img
                  :src="$assetPath + 'document.svg'"
                  class="svg-icon va-middle static"
                  height="32"
                >
                {{ file.name }}
                <span
                  v-if="!uploadQueueActive"
                  class="icon-span is-24"
                  title="Noņemt failu"
                  @click="removeFromQueue(index)"
                >
                  <svgicon
                    class="svg-icon va-middle icon-blueish-darker-still"
                    name="close-circle"
                    height="24"
                  />
                </span>
              </div>
            </transition-group>
          </div>
        </transition>
        <!-- FILE QUEUE END -->
      </section>
      <div
        v-if="sliderOpen"
        class="bb-blueish mt-24"/>
    </div>
    <div class="section-bg scroll-section">
      <section class="section">
        <div
          v-if="!files.length"
          class="empty-file-list"
        >
          {{ $lang.messages.upload_file_to_start }}
        </div>
        <file-list-container
          v-show="totalFiles > 0"
          :file-list="files"
          class="mb-48"
          @deleted="fetchFileList(currentPage, true)"
        />
        <file-list-pager
          :pages="totalPages"
          @pageChanged="fetchFileList"
        />
      </section>
    </div>
    <transition
      name="ffade">
      <settings-panel
        v-if="settingsOpen"
        :key="1"
        @closeSettings="() => { settingsOpen = false }"
      />
    </transition>
  </div>
</template>

<script>
import FileService from 'services/file'
import _ from 'lodash'
import {DateConverter} from 'utils/date-converter'
import FileListToolbar from 'components/file-list/FileListToolbar'
import FileListSelector from 'components/file-list/FileListSelector'
import FileListContainer from 'components/file-list/FileListContainer'
import FileListPager from 'components/file-list/FileListPager'
import {FileConstructor} from 'utils/file-constructor'
import {FormGenerator} from 'services/form-generator'
import SettingsPanel from 'components/SettingsPanel'
export default {
  name: 'FileList',
  components: {
    'file-list-toolbar': FileListToolbar,
    'file-list-selector': FileListSelector,
    'file-list-container': FileListContainer,
    'file-list-pager': FileListPager,
    'settings-panel': SettingsPanel
  },
  data: function () {
    return {
      files: [],
      uploadQueue: [],
      subject: null,
      toLang: null,
      fromLang: null,
      sliderOpen: true,
      recordsPerPage: 10,
      totalPages: 1,
      totalFiles: 0,
      currentPage: 1,
      tmpFileId: 0,
      lastUpload: new Date().getTime(),
      uploadThrottleTime: 1000,
      uploadQueueActive: false,
      settingsOpen: false
    }
  },
  mounted: function () {
    this.fetchFileList(1)
  },
  methods: {
    fetchFileList: function (page, updateList) {
      updateList = updateList || false
      this.currentPage = page
      const data = {
        id_team: this.$store.getters.profile.teamId,
        page: page,
        filter: 0
      }
      FileService.getList(data)
        .then(response => {
          if (!updateList) {
            this.files = null
          }
          this.totalFiles = parseInt(response.data.pnumber)
          if (this.totalFiles === 0) {
            this.sliderOpen = true
          }
          this.updatePagesCount()
          this.files = _.map(response.data.data, el => {
            if (updateList) {
              const exists = _.find(this.files, {id: el.id})
              if (exists) return exists
            }
            const link = this.$CONFIG.baseUrl + 'api/v1/jobs/' + el.jobs[0].id + '/' + el.jobs[0].password + '/stats'
            // Call file download urls
            this.getFileUrls(el.id, el.password)
            // Call file status check to get missing data
            FileService.checkStatus(link)
              .then(this.statsResponse)
            // Call file analyze in case it's not yet finished
            FileService.analyze({
              pid: el.id,
              ppassword: el.password
            })
              .then(this.analyzeResponse)
            // Return incomplete file data
            return FileConstructor.get({
              id: el.id,
              password: el.password,
              jobId: el.jobs[0].id,
              jobPassword: el.jobs[0].password,
              name: el.name,
              owner: el.jobs[0].owner,
              created: DateConverter.timeStampToDate(el.jobs[0].create_timestamp),
              tmpFileId: this.tmpFileId++,
              statsLink: link,
              direction: el.jobs[0].source + ' - ' + el.jobs[0].target
            })
          })
        })
    },
    upload: function (file, fileName, fileTmpId) {
      let langDetect = {}
      langDetect[fileName] = 'detect'
      let formData = FormGenerator.generateForm({
        file_name: fileName,
        source_lang: this.fromLang,
        target_lang: this.toLang,
        segmentation_rule: '',
        project_name: '',
        source_language: this.fromLang,
        target_language: this.toLang,
        job_subject: this.subject.value,
        disable_tms_engine: 'false',
        mt_engine: '1',
        private_key_list: '{"ownergroup":[],"mine":[],"anonymous":[]}',
        langDetect: langDetect,
        pretranslate_100: '0',
        lexiqa: 'false',
        speech2text: 'false',
        tag_projection: 'true',
        dqf: 'false',
        get_public_matches: 'true'
      })
      formData.append('files[]', file)
      FileService.upload(formData)
        .then(res => {
          if (res.data.code === -6) {
            const fileIndex = _.findKey(this.files, {tmpFileId: fileTmpId})
            if (fileIndex > -1) {
              this.files.splice(parseInt(fileIndex), 1)
            }
            const queueIndex = _.findKey(this.uploadQueue, {tmpId: fileTmpId})
            if (queueIndex > -1) {
              this.uploadQueue.splice(parseInt(queueIndex), 1)
            }
            if (this.uploadQueue.length < 1) this.uploadQueueActive = false
            this.updatePagesCount()
            return Promise.reject(new Error(this.$lang.messages.error_uploading_file + res.data.errors[0].debug))
          }
          const file = _.find(this.files, {tmpFileId: fileTmpId})
          file.id = res.data.data.id_project
          file.password = res.data.data.password
          file.statusLink = this.$CONFIG.baseUrl + 'api/v2/projects/' + res.data.data.id_project + '/' + res.data.data.password + '/creation_status'
          setTimeout(() => {
            FileService.checkStatus(file.statusLink)
              .then(this.statusResponse)
              .catch(this.statusResponseError)
          }, 1500)
        })
        .catch(this.statusResponseError)
    },
    statusResponse: function (res) {
      // If file is not done processing keep calling checkStatus until it is
      if (res.data.status === 202) {
        setTimeout(() => {
          FileService.checkStatus(res.request.responseURL)
            .then(this.statusResponse)
        }, 2000)
      }
      if (res.data.status === 200) {
        const file = _.find(this.files, {statusLink: res.request.responseURL})
        this.getFileUrls(file.id, file.password)
        FileService.analyze({
          pid: file.id,
          ppassword: file.password
        })
          .then(this.analyzeResponse)
      }
    },
    analyzeResponse: function (res) {
      const file = _.find(this.files, {id: parseInt(res.data.data.project_id)})
      if (res.data.data.summary.STATUS === 'DONE') {
        file.wordCount = parseInt(res.data.data.summary.TOTAL_RAW_WC)
        file.segmentCount = parseInt(res.data.data.summary.TOTAL_SEGMENTS)
        if (file.jobId < 0) {
          file.jobId = Object.keys(res.data.data.jobs)[0]
          file.jobPassword = Object.keys(Object.values(res.data.data.jobs)[0].totals)[0]
          file.progress = 0.00
          file.created = DateConverter.nowDate()
          file.owner = this.$store.state.profile.email
          this.nextFileUpload()
        }
        return
      }
      if (res.data.data.summary.STATUS !== 'EMPTY') {
        setTimeout(() => {
          FileService.analyze({
            pid: file.id,
            ppassword: file.password
          })
            .then(this.analyzeResponse)
        }, 2000)
        return
      }
      // TODO What to do with EMPTY file?
      file.isEmpty = true
      this.nextFileUpload()
    },
    statsResponse: function (res) {
      const file = _.find(this.files, {statsLink: res.request.responseURL})
      if (file.isEmpty) return
      if (!res.data.stats.ANALYSIS_COMPLETE) {
        setTimeout(() => {
          FileService.checkStatus(res.request.responseURL)
            .then(this.statsResponse)
        }, 2000)
      } else {
        file.progress = parseFloat(res.data.stats.TRANSLATED_PERC).toFixed(2)
      }
    },
    statusResponseError: function (err) {
      this.nextFileUpload()
      if (err.message === 'Request failed with status code 403') {
        this.$Alerts.add(this.$lang.messages.unexpected_error)
        return
      }
      this.$Alerts.add(err.message)
    },
    getFileUrls: function (id, password) {
      FileService.getUrls({id_project: id, password: password})
        .then(r => {
          const file = _.find(this.files, { id: id })
          if (typeof (r.data.urls.files[0]) !== 'undefined') {
            file.translatedUrl = r.data.urls.files[0].translation_download_url
          }
        })
    },
    sendToUploadQueue: function (file) {
      this.uploadQueue.push(file)
    },
    nextFileUpload: function () {
      if (this.uploadQueue.length < 1) {
        this.uploadQueueActive = false
        this.$loading.endLoading('translator')
        return
      }
      this.$loading.startLoading('translator')
      this.uploadQueueActive = true
      const file = this.uploadQueue.splice(0, 1)[0]
      let fileTmpId = this.tmpFileId++
      if (this.files.length === this.recordsPerPage) {
        this.files.splice(-1, 1)
      }
      this.files.unshift(FileConstructor.get(
        {
          name: file.name,
          created: DateConverter.nowDate(),
          tmpFileId: fileTmpId,
          direction: this.fromLang + ' - ' + this.toLang
        }
      ))
      this.totalFiles++
      this.updatePagesCount()
      this.upload(file, file.name, fileTmpId)
    },
    updatePagesCount: function () {
      const pages = Math.ceil(this.totalFiles / this.recordsPerPage)
      this.totalPages = isNaN(pages) ? 1 : pages
    },
    removeFromQueue: function (index) {
      this.uploadQueue.splice(index, 1)
    }
  }
}
</script>
