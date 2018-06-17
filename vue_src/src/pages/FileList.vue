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
          class="head-control"
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
            @fileAdded="sendToUploadQueue"
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
        <file-list-pager
          :pages="totalPages"
          @pageChanged="fetchFileList"
        />
      </div>
    </div>
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
      subject: null,
      toLang: null,
      fromLang: null,
      sliderOpen: false,
      recordsPerPage: 10,
      totalPages: 1,
      tmpFileId: 0,
      lastUpload: new Date().getTime(),
      uploadThrottleTime: 1000,
      uploadQueue: [],
      uploadQueueInterval: null
    }
  },
  mounted: function () {
    this.fetchFileList(1)
  },
  methods: {
    fetchFileList: function (page) {
      // Get first page of files
      const data = {
        id_team: this.$store.getters.profile.teamId,
        page: page,
        filter: 0
      }
      FileService.getList(data)
        .then(response => {
          this.files = null
          const pages = Math.ceil(parseInt(response.data.pnumber) / this.recordsPerPage)
          this.totalPages = isNaN(pages) ? 1 : pages
          this.files = _.map(response.data.data, el => {
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
              statsLink: link
            })
          })
        })
    },
    upload: function (file, fileName, fileTmpId) {
      // eslint-disable-next-line no-undef
      let formData = new FormData()
      formData.append('files[]', file)
      FileService.upload(formData)
        .then(uploadRes => {
          const convertData = {
            action: 'convertFile',
            file_name: fileName,
            source_lang: this.fromLang.value,
            target_lang: this.toLang.value,
            segmentation_rule: ''
          }
          return FileService.convert(convertData)
        })
        .then(convertRes => {
          // TODO What to do with convertData?
          let langDetect = {}
          langDetect[fileName] = 'detect'
          const createData = {
            action: 'createProject',
            project_name: '',
            file_name: fileName,
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
          const file = _.find(this.files, {tmpFileId: fileTmpId})
          file.id = projectRes.data.data.id_project
          file.password = projectRes.data.data.password
          file.statusLink = this.$CONFIG.baseUrl + 'api/v2/projects/' + projectRes.data.data.id_project + '/' + projectRes.data.data.password + '/creation_status'
          return FileService.checkStatus(file.statusLink)
        })
        .then(this.statusResponse)
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
      console.log(err)
      this.$Alerts.add('File status error')
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
      let fileTmpId = this.tmpFileId++
      this.files.unshift(FileConstructor.get(
        {
          name: file.name,
          created: DateConverter.nowDate(),
          tmpFileId: fileTmpId
        }
      ))
      this.uploadQueue.push({
        file: file,
        tmpId: fileTmpId
      })
      if (this.uploadQueueInterval !== null) return
      this.uploadQueueInterval = setInterval(() => {
        const record = this.uploadQueue.splice(0, 1)[0]
        this.upload(record.file, record.file.name, record.tmpId)
        if (this.uploadQueue.length < 1) {
          clearInterval(this.uploadQueueInterval)
        }
      }, this.uploadThrottleTime)
    }
  }
}
</script>
