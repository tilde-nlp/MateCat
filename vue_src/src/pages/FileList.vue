<template>
  <div class="page-container">
    <div class="section-bg bg-grey-light">
      <section class="section">
        <!-- BREADCRUMBS -->
        <div class="bread-crumbs">
          <div class="bc-nav">Sākums</div>
          /
          <div class="bc-nav inactive">Tulkošanas asistents</div>
        </div>
        <!-- BREADCRUMBS END -->
        <!-- TITLE -->
        <div class="h2 mt-24 mb-24">Tulkošanas asistents</div>
        <!-- TITLE END -->
      </section>
      <div class="bb-blueish"/>
      <section class="section">
        <!-- FILE UPLOAD TOGGLE -->
        <div
          class="slider-opener"
          @click="() => {sliderOpen = !sliderOpen}"
        >
          <svgicon
            class="svg-icon va-middle"
            name="file"
            height="24"
          />
          <div class="link ib">Tulkot failus</div>
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
            @fromLangChange="value => { fromLang = value }"
            @toLangChange="value => { toLang = value }"
            @subjectChange="value => { subject = value }"
          />
          <file-list-selector
            @fileAdded="processNewFile"
          />
        </div>
        <!-- FILE UPLOAD CONTAINER END -->
      </section>
      <div
        v-if="sliderOpen"
        class="bb-blueish mt-24"/>
    </div>
    <div class="section-bg">
      <div class="section mb-48">
        <file-list-container
          :file-list="files"
        />
      </div>
    </div>
    <div class="section-bg">
      <div class="section mb-128">
        <file-list-pager/>
      </div>
    </div>
  </div>
</template>

<script>
import FileService from 'services/file'
import _ from 'lodash'
import Vue from 'vue'
import {DateConverter} from 'utils/date-converter'
import FileListToolbar from 'components/file-list/FileListToolbar'
import FileListSelector from 'components/file-list/FileListSelector'
import FileListContainer from 'components/file-list/FileListContainer'
import FileListPager from 'components/file-list/FileListPager'
export default {
  name: 'FileList',
  components: {
    'file-list-toolbar': FileListToolbar,
    'file-list-selector': FileListSelector,
    'file-list-container': FileListContainer,
    'file-list-pager': FileListPager
  },
  data: function () {
    return {
      files: [],
      pendingFiles: [],
      uploadProgress: {},
      getterProgress: {},
      subject: null,
      toLang: null,
      fromLang: null,
      sliderOpen: false
    }
  },
  mounted: function () {
    this.fetchFileList()
  },
  methods: {
    fetchFileList: function () {
      // Get first page of files
      const data = {
        id_team: this.$store.getters.profile.teamId,
        page: 1,
        filter: 0
      }
      FileService.getList(data)
        .then(response => {
          this.files = null
          this.files = _.map(response.data.data, el => {
            // Set file data we already have
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
            // Call file analyze in case it's not yet finished
            FileService.analyze(data)
              .then(this.analyzeResponseForGetter)
            // Call file status check to get missing data
            FileService.checkStatus(link)
              .then(this.statusResponseGetter)
            // Call file download urls
            FileService.getUrls({id_project: el.id, password: el.password})
              .then(r => {
                const file = _.find(this.files, { id: el.id })
                file.translatedUrl = r.data.urls.files[0].translation_download_url
              })
            // Return incomplete file data
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
    },
    upload: function (file, index) {
      // eslint-disable-next-line no-undef
      let formData = new FormData()
      formData.append('files[]', file)
      FileService.upload(formData)
        .then(uploadRes => {
          this.uploadProgress[index].fileName = uploadRes.data[0].name
          const convertData = {
            action: 'convertFile',
            file_name: this.uploadProgress[index].fileName,
            source_lang: this.fromLang.value,
            target_lang: this.toLang.value,
            segmentation_rule: ''
          }
          return FileService.convert(convertData)
        })
        .then(convertRes => {
          // TODO What to do with convertData?
          let langDetect = {}
          langDetect[this.uploadProgress[index].fileName] = 'detect'
          const createData = {
            action: 'createProject',
            project_name: '',
            file_name: this.uploadProgress[index].fileName,
            source_language: this.fromLang.value,
            target_language: this.toLang.value,
            job_subject: this.subject.value,
            disable_tms_engine: 'false',
            mt_engine: '1',
            private_key_list: '{"ownergroup":[],"mine":[],"anonymous":[]}',
            langDetect: langDetect,
            pretranslate_100: '0',
            lexiqa: 'false',
            speech2text: 'false',
            tag_projection: 'true',
            segmentation_rule: '',
            dqf: 'false',
            get_public_matches: 'true'
          }
          return FileService.createProject(createData)
        })
        .then(projectRes => {
          this.uploadProgress[index].projectId = projectRes.data.data.id_project
          this.uploadProgress[index].password = projectRes.data.data.password
          this.uploadProgress[index].link = this.$CONFIG.baseUrl + 'api/v2/projects/' + projectRes.data.data.id_project + '/' + projectRes.data.data.password + '/creation_status'
          this.files[index].loadingProgress = 'ANALYZE'
          return FileService.checkStatus(this.uploadProgress[index].link)
        })
        .then(this.statusResponse)
    },
    statusResponse: function (res) {
      // If file is not done processing keep calling checkStatus until it is
      if (res.data.status === 202) {
        setTimeout(() => {
          FileService.checkStatus(res.request.responseURL)
            .then(this.statusResponse)
        }, 1000)
      }
      if (res.data.status === 200) {
        const currentUpload = _.find(Object.values(this.uploadProgress), {link: res.request.responseURL})
        FileService.analyze({
          pid: currentUpload.projectId,
          ppassword: currentUpload.password
        })
          .then(this.analyzeResponse)
      }
    },
    analyzeResponse: function (res) {
      const currentUpload = _.find(Object.values(this.uploadProgress), {projectId: parseInt(res.data.data.project_id)})
      if (res.data.data.summary.STATUS !== 'DONE') {
        setTimeout(() => {
          FileService.analyze({
            pid: currentUpload.projectId,
            ppassword: currentUpload.password
          })
            .then(this.analyzeResponse)
        }, 1000)
      } else {
        this.files[currentUpload.index].jobId = Object.keys(res.data.data.jobs)[0]
        this.files[currentUpload.index].jobPassword = Object.keys(Object.values(res.data.data.jobs)[0].totals)[0]
        this.files[currentUpload.index].wordCount = parseInt(res.data.data.summary.TOTAL_RAW_WC)
        this.files[currentUpload.index].segmentCount = parseInt(res.data.data.summary.TOTAL_SEGMENTS)
        this.files[currentUpload.index].progress = 0.00
        this.files[currentUpload.index].created = DateConverter.nowDate()
        this.files[currentUpload.index].owner = this.$store.state.profile.email
        this.$loading.endLoading('file_' + currentUpload.index)
        Vue.delete(this.uploadProgress, currentUpload.index)
      }
    },
    analyzeResponseForGetter: function (res) {
      let currentGetter = _.find(Object.values(this.getterProgress), {projectId: parseInt(res.data.data.project_id)})
      if (res.data.data.summary.STATUS !== 'DONE') {
        setTimeout(() => {
          FileService.analyze({
            pid: currentGetter.projectId,
            ppassword: currentGetter.password
          })
            .then(this.analyzeResponseForGetter)
        }, 1000)
      } else {
        let currentFile = _.find(this.files, {id: currentGetter.projectId})
        currentFile.wordCount = parseInt(res.data.data.summary.TOTAL_RAW_WC)
        currentFile.segmentCount = parseInt(res.data.data.summary.TOTAL_SEGMENTS)
      }
    },
    statusResponseGetter: function (res) {
      const currentGetter = _.find(Object.values(this.getterProgress), {link: res.request.responseURL})
      if (!res.data.stats.ANALYSIS_COMPLETE) {
        setTimeout(() => {
          FileService.checkStatus(currentGetter.link)
            .then(this.statusResponseGetter)
        }, 1000)
      } else {
        const currentFile = _.find(this.files, {id: currentGetter.projectId})
        currentFile.progress = parseFloat(res.data.stats.TRANSLATED_PERC).toFixed(2)
      }
    },
    processNewFile: function (file) {
      const index = this.files.length
      this.uploadProgress[index] = {
        index: index,
        status: 'Augšupielādējas',
        projectId: '',
        password: '',
        fileName: '',
        link: ''
      }
      this.files.push({
        name: file.name,
        wordCount: 0,
        loadingStatus: 'UPLOADING'
      })
      this.$loading.startLoading('file_' + index)
      this.upload(file, index)
    }
  }
}
</script>
