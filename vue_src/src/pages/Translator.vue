<template>
  <div
    v-shortkey="{ add: ['ctrl', 'arrowright'], sub: ['ctrl', 'arrowleft'] }"
    id="catTranslator"
    class="page-container"
    @shortkey="fontControl"
  >
    <div class="section-bg bg-grey-light">
      <div class="bb-blueish"/>
      <section class="section header">
        <translator-toolbox
          :job-data="jobData"
          @confirm="() => setStatus('translated')"
          @sourceToTarget="copySourceToTarget"
          @clear="clearTranslation"
          @toPrevious="searchUnconfirmed(-1)"
          @toNext="searchUnconfirmed(1)"
          @toggleSplit="toggleSplit"
        />
      </section>
      <div class="bb-blueish"/>
    </div>
    <div class="section-bg bg-white h-100p">
      <section class="section triple-block-container h-100p">
        <div class="triple-block double">
          <div class="double-block">
            <div class="relative ml-32 mt-8 mb-8 mr-8">
              <svgicon
                class="svg-icon icon-blueish-darker-still placeholder"
                name="search"
                height="24"
              />
              <input
                :placeholder="$lang.inputs.search_in_original"
                class="search-input"
                type="text"
              >
            </div>
          </div>
          <div class="double-block">
            <div class="relative mt-8 mb-8 mr-32">
              <svgicon
                class="svg-icon icon-blueish-darker-still placeholder"
                name="search"
                height="24"
              />
              <input
                :placeholder="$lang.inputs.search_in_translation"
                class="search-input"
                type="text"
              >
            </div>
          </div>
          <div class="double-block br-light-darker">
            <div class="number-col bl-light-darker header">
              <div class="ma">#</div>
            </div>
            <div class="segment-col header first">
              {{ $lang.titles.original }}: {{ jobData.source }}
            </div>
          </div>
          <div class="double-block">
            <div class="segment-col header last">
              {{ $lang.titles.translation }}: {{ jobData.target }}
            </div>
            <div class="tools-col header no-border">
              &nbsp;
            </div>
          </div>
          <div
            id="translatorSegments"
            :style="{'max-height': segmentListHeight + 'px'}"
            class="segments-container"
            @scroll="segmentsScrolled"
          >
            <translator-segment
              v-for="(segment, index) in segmentsList"
              :key="index"
              :segment-data="segment"
              :first-segment-id="jobData.firstSegment"
              :font-size="fontSize"
              :top-segment="index === 0"
              :split-active="splitActive"
              @click="setActive"
              @setStatus="setStatus"
              @inputDebounce="onInputDebounce"
            />
          </div>
          <div class="segments-footer">
            <div class="ib ml-8">{{ jobData.fileName }}</div>
            <div class="pull-right mr-8">{{ $lang.titles.translated }} {{ jobData.progress }}% {{ $lang.titles.from }} {{ jobData.segments }} {{ $lang.titles.from_segments }}</div>
            <div class="clear-both"/>
            <div
              :style="{width: jobData.progress + '%' }"
              class="progress-bar"
            />
            <div
              :style="{width: (100 - jobData.progress) + '%' }"
              class="progress-bar-empty"
            />
          </div>
        </div>
        <translator-assistant
          :active-segment="activeSegment"
          :job-data="jobData"
          :max-height="suggestionsListHeight"
          @mtSystemChange="val => { system = val.value }"
        />
      </section>
    </div>
  </div>
</template>

<script>
import _ from 'lodash'
import Vue from 'vue'
import SegmentsService from 'services/segments'
import TranslatorToolbox from 'components/translator/TranslatorToolbox'
import TranslatorSegment from 'components/translator/TranslatorSegment'
import TranslatorAssistant from 'components/translator/TranslatorAssistant'
import JobsService from 'services/jobs.js'
import FileService from 'services/file.js'
export default {
  name: 'Translator',
  components: {
    'translator-toolbox': TranslatorToolbox,
    'translator-segment': TranslatorSegment,
    'translator-assistant': TranslatorAssistant
  },
  data: function () {
    return {
      segments: [],
      fileId: '',
      activeSegment: {},
      fontSize: null,
      system: '',
      segmentListHeight: 600,
      suggestionsListHeight: 500,
      splitActive: false,
      jobData: {
        id: 0,
        password: '',
        projectId: 0,
        ppassword: '',
        source: '',
        target: '',
        lastSegmentId: 0,
        progress: 0,
        segments: 0,
        translatedUrl: '',
        originalUrl: '',
        fileName: '',
        firstSegment: 0,
        lastSegment: 0
      }
    }
  },
  computed: {
    segmentsList: function () {
      const l = _.sortBy(this.segments, ['id'], ['asc'])
      return l
    }
  },
  mounted: function () {
    this.jobData.id = this.$route.params.jobId
    this.jobData.password = this.$route.params.password
    this.jobData.projectId = this.$route.params.projectId
    this.jobData.ppassword = this.$route.params.ppassword
    this.fontSize = this.$cookie.get('fontSize') === null ? 15 : parseInt(this.$cookie.get('fontSize'))
    JobsService.getInfo({
      id: this.jobData.id,
      password: this.jobData.password
    })
      .then(jobRes => {
        this.jobData.lastSegmentId = parseInt(jobRes.data.active_segment_id)
        this.jobData.source = jobRes.data.source
        this.jobData.target = jobRes.data.target
        this.jobData.firstSegment = parseInt(jobRes.data.firstSegment)
        this.jobData.lastSegment = parseInt(jobRes.data.lastSegment)
        this.jobData.fileName = jobRes.data.fileName
        this.fetchSegments()
        this.checkStats()
        this.getFileUrls()
      })
    this.$nextTick(function () {
      window.addEventListener('resize', this.setSegmentListHeight)
      this.setSegmentListHeight()
    })
  },
  beforeDestroy: function () {
    window.removeEventListener('resize', this.setSegmentListHeight)
  },
  methods: {
    setSegmentListHeight: function () {
      const appHeight = document.getElementById('cat-app').clientHeight
      this.segmentListHeight = appHeight - 48 - 26 - 90
      this.suggestionsListHeight = appHeight - 48 - 6 - 167
    },
    checkStats: function () {
      const link = this.$CONFIG.baseUrl + 'api/v1/jobs/' + this.jobData.id + '/' + this.jobData.password + '/stats'
      // Call file status check to get missing data
      FileService.checkStatus(link)
        .then(this.statsResponse)
      FileService.analyze({
        pid: this.jobData.projectId,
        ppassword: this.jobData.ppassword
      })
        .then(this.analyzeResponse)
    },
    statsResponse: function (res) {
      if (!res.data.stats.ANALYSIS_COMPLETE) {
        setTimeout(() => {
          FileService.checkStatus(res.request.responseURL)
            .then(this.statsResponse)
        }, 2000)
      } else {
        this.jobData.progress = parseFloat(res.data.stats.TRANSLATED_PERC).toFixed(2)
      }
    },
    analyzeResponse: function (res) {
      if (res.data.data.summary.STATUS === 'DONE') {
        this.jobData.segments = parseInt(res.data.data.summary.TOTAL_SEGMENTS)
        return
      }
      if (res.data.data.summary.STATUS !== 'EMPTY') {
        setTimeout(() => {
          FileService.analyze({
            pid: this.jobData.projectId,
            ppassword: this.jobData.ppassword
          })
            .then(this.analyzeResponse)
        }, 2000)
      }
    },
    fetchSegments: function () {
      let data = {
        action: 'getSegments',
        jid: this.jobData.id,
        password: this.jobData.password,
        where: 'center',
        step: 5
      }
      if (this.jobData.lastSegmentId > 0) {
        data['segment'] = this.jobData.lastSegmentId
      }
      SegmentsService.getSegments(data)
        .then(r => {
          this.fileId = Object.keys(r.data.data.files)[0]
          this.segments = _.map(Object.values(r.data.data.files)[0].segments, el => {
            return {
              id: parseInt(el.sid),
              original: el.segment,
              translation: el.translation,
              status: (el.status === 'TRANSLATED' ? 'done' : ''),
              active: false,
              version: el.version,
              suggestions: [],
              suggestionsLoaded: false,
              jobId: this.jobData.id,
              jobPassword: this.jobData.password,
              saveType: el.save_type,
              match: el.save_match
            }
          })
          if (this.jobData.lastSegmentId > 0) {
            this.setActive(this.jobData.lastSegmentId)
          } else {
            this.setActive(this.segments[0].id)
          }
          this.segmentsScrolled()
        })
    },
    getContribution: function (segment) {
      const context = this.getContext(segment)
      const data = {
        action: 'getContribution',
        password: this.jobData.password,
        is_concordance: 0,
        id_segment: segment.id,
        text: segment.original,
        id_job: this.jobData.id,
        num_results: 5,
        context_before: context.before,
        context_after: context.after,
        letsmt_system: this.system
      }
      SegmentsService.getContribution(data)
        .then(r => {
          segment.suggestions = null
          segment.suggestions = _.map(r.data.data.matches, el => {
            const isMT = el.created_by === 'MT'
            return {
              createdBy: el.created_by,
              match: isMT ? 'MT' : parseInt(el.match) + '%',
              rawMatch: parseInt(el.match),
              translation: el.translation,
              isMT: isMT,
              segment: el.segment,
              lastUpdatedBy: el.last_updated_by,
              usageCount: el.usage_count
            }
          })
          segment.suggestionsLoaded = true
        })
    },
    getContext: function (segment) {
      const index = _.findKey(this.segments, item => {
        return item.id === segment.id
      })
      return {
        before: (typeof (this.segments[index - 1]) === 'undefined') ? '' : this.segments[index - 1].original,
        after: (typeof (this.segments[index + 1]) === 'undefined') ? '' : this.segments[index + 1].original
      }
    },
    setStatus: function (status) {
      if (this.activeSegment === null) return
      const context = this.getContext(this.activeSegment)
      const data = {
        id_segment: this.activeSegment.id,
        id_job: this.jobData.id,
        id_first_file: this.fileId,
        password: this.jobData.password,
        status: status,
        translation: this.activeSegment.translation,
        segment: this.activeSegment.original,
        time_to_edit: 1,
        autosave: false,
        version: this.activeSegment.version,
        propagate: true,
        context_before: context.before,
        context_after: context.after,
        action: 'setTranslation',
        saveType: this.activeSegment.saveType,
        saveMatch: this.activeSegment.match
      }
      SegmentsService.setTranslation(data)
        .then(() => {
          this.activeSegment.status = (status === 'translated' ? 'done' : '')
          if (status === 'translated') {
            const activeIndex = parseInt(_.findKey(this.segments, {id: this.activeSegment.id}))
            if (activeIndex + 1 < this.segments.length) {
              this.searchUnconfirmed(1)
            }
          }
          this.checkStats()
        })
    },
    setActive: function (id) {
      if (id === this.activeSegment.id) {
        return
      }
      if (this.activeSegment.status !== 'done') {
        this.setStatus('draft')
      }
      _.map(this.segments, e => {
        if (e.id === id) {
          e.active = true
          this.jobData.lastSegmentId = e.id
          this.activeSegment = e
          if (e.suggestions.length === 0) this.getContribution(e)
          const data = {
            action: 'setCurrentSegment',
            password: e.jobPassword,
            id_segment: e.id,
            id_job: e.jobId
          }
          SegmentsService.setCurrent(data)
        } else {
          e.active = false
        }
        return e
      })
      const activeIndex = parseInt(_.findKey(this.segments, {active: true}))
      if (activeIndex === 0 || this.segments.length - activeIndex - 1 === 0) {
        this.readMoreSegments(activeIndex)
      }
    },
    readMoreSegments: function (activeIndex, callback) {
      callback = callback || null
      let data = {
        action: 'getSegments',
        jid: this.jobData.id,
        password: this.jobData.password,
        where: activeIndex === 0 ? 'before' : 'after',
        step: 5,
        segment: this.segments[activeIndex].id
      }
      SegmentsService.getSegments(data)
        .then(r => {
          if (Object.values(r.data.data.files).length < 1) {
            if (callback !== null) callback()
            return
          }
          let newArray = []
          _.map(Object.values(r.data.data.files)[0].segments, el => {
            const item = {
              id: parseInt(el.sid),
              original: el.segment,
              translation: el.translation,
              status: (el.status === 'TRANSLATED' ? 'done' : ''),
              active: false,
              version: el.version,
              suggestions: [],
              suggestionsLoaded: false,
              jobId: this.jobData.id,
              jobPassword: this.jobData.password,
              saveType: '',
              match: 0
            }
            if (activeIndex === 0) {
              newArray.push(item)
            } else {
              this.segments.push(item)
            }
          })
          if (activeIndex === 0) {
            newArray = newArray.concat(this.segments)
            this.segments = null
            Vue.nextTick(() => {
              this.segments = newArray
            })
          }
          if (callback !== null) callback()
        })
    },
    fontControl: function (event) {
      switch (event.srcKey) {
        case 'add':
          this.fontSize++
          break
        case 'sub':
          this.fontSize--
          break
      }
      this.$cookie.set('fontSize', this.fontSize, 720)
    },
    getFileUrls: function () {
      FileService.getUrls({id_project: this.jobData.projectId, password: this.jobData.ppassword})
        .then(r => {
          if (typeof (r.data.urls.files[0]) !== 'undefined') {
            Vue.set(this.jobData, 'translatedUrl', r.data.urls.files[0].translation_download_url)
            Vue.set(this.jobData, 'originalUrl', r.data.urls.files[0].original_download_url)
          }
        })
    },
    copySourceToTarget: function () {
      if (this.activeSegment === null) return
      this.activeSegment.translation = this.activeSegment.original
      this.setStatus('draft')
    },
    clearTranslation: function () {
      if (this.activeSegment === null) return
      this.activeSegment.translation = ''
      this.activeSegment.save_type = null
      this.activeSegment.save_match = null
      this.setStatus('draft')
    },
    searchUnconfirmed: function (direction, activeIndex) {
      activeIndex = activeIndex || parseInt(_.findKey(this.segments, {active: true}))
      let segmentId = 0
      while (1) {
        activeIndex += direction
        if (activeIndex < 0) {
          activeIndex = 0
          segmentId = this.segments[activeIndex].id
          this.readMoreSegments(activeIndex, () => {
            const newActiveIndex = parseInt(_.findKey(this.segments, {id: segmentId}))
            if (newActiveIndex !== activeIndex) {
              this.searchUnconfirmed(direction, newActiveIndex)
            }
          })
          break
        }
        if (activeIndex >= this.segments.length) {
          activeIndex = this.segments.length - 1
          segmentId = this.segments[activeIndex].id
          this.readMoreSegments(activeIndex, () => {
            const newActiveIndex = parseInt(_.findKey(this.segments, {id: segmentId}))
            if (newActiveIndex !== activeIndex) {
              this.searchUnconfirmed(direction, newActiveIndex)
            }
          })
          break
        }
        if (this.segments[activeIndex].status !== 'done') {
          this.setActive(this.segments[activeIndex].id)
          break
        }
      }
    },
    segmentsScrolled: function () {
      if (!this.segments.length) {
        return
      }
      const element = document.getElementById('translatorSegments')
      if (element.scrollTop === (element.scrollHeight - element.offsetHeight)) {
        this.readMoreSegments(this.segments.length - 1)
        return
      }
      if (element.scrollTop === 0) {
        this.readMoreSegments(0)
      }
    },
    setSegmentSplit: function () {
      const data = {
        action: 'setSegmentSplit',
        segment: this.activeSegment.original,
        id_segment: this.activeSegment.id,
        id_job: this.activeSegment.jobId,
        password: this.activeSegment.jobPassword
      }
      SegmentsService.setSegmentSplit(data)
    },
    toggleSplit: function () {
      this.splitActive = !this.splitActive
      if (!this.splitActive) {
        this.setSegmentSplit()
      }
    },
    onInputDebounce: function () {
      this.setStatus('draft')
    }
  }
}
</script>
