<template>
  <div
    v-shortkey="{ add: ['ctrl', 'arrowright'], sub: ['ctrl', 'arrowleft'] }"
    class="page-container"
    @shortkey="fontControl"
  >
    <div class="section-bg bg-grey-light">
      <div class="bb-blueish"/>
      <section class="section">
        <!-- BACK -->
        <div
          class="head-control w-192"
          @click="goBack"
        >
          <svgicon
            class="svg-icon va-middle"
            name="arrow"
            height="24"
          />
          <div class="link ib">Atpakaļ uz sarakstu</div>
        </div>
        <!-- BACK END -->
        <!-- SETTINGS TOGGLE -->
        <div
          class="head-control w-135 settings"
          @click="() => { settingsOpen = !settingsOpen }"
        >
          <svgicon
            class="svg-icon va-middle"
            name="toolbar"
            height="24"
          />
          <div class="link ib">Rīkjosla</div>
          <svgicon
            :class="{open: settingsOpen}"
            class="svg-icon va-middle chevron"
            name="chevron"
            height="24"
          />
        </div>
        <!-- SETTINGS TOGGLE END -->
      </section>
      <div class="bb-blueish"/>
      <section class="section">
        <translator-toolbox
          :class="{open: settingsOpen}"
          class="slider-container"
          @confirm="() => setStatus('translated')"
        />
      </section>
      <div
        v-if="settingsOpen"
        class="bb-blueish mt-16"/>
    </div>
    <div class="section-bg bg-white h-100p">
      <section class="section triple-block-container h-100p">
        <div class="triple-block double">
          <div class="double-block">
            <div class="number-col bl-light-darker header">
              <div class="ma">#</div>
            </div>
            <div class="segment-col header first">
              Orģināls: en
            </div>
          </div>
          <div class="double-block">
            <div class="segment-col header last">
              Tulkojums: lv
            </div>
            <div class="tools-col header">
              &nbsp;
            </div>
          </div>
          <div
            :style="{'max-height': '600px'}"
            class="segments-container"
          >
            <translator-segment
              v-for="(segment, index) in segmentsList"
              :key="index"
              :segment-data="segment"
              :font-size="fontSize"
              :nr="index + 1"
              @click="setActive"
              @setStatus="setStatus"
            />
          </div>
        </div>
        <translator-assistant
          :active-segment="activeSegment"
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
      settingsOpen: false,
      activeSegment: {},
      fontSize: null,
      lastSegmentId: 0,
      system: ''
    }
  },
  computed: {
    segmentsList: function () {
      const l = _.sortBy(this.segments, ['id'], ['asc'])
      return l
    }
  },
  mounted: function () {
    this.fontSize = this.$cookie.get('fontSize') === null ? 15 : parseInt(this.$cookie.get('fontSize'))
    JobsService.getInfo({
      id: this.$route.params.jobId,
      password: this.$route.params.password
    })
      .then(jobRes => {
        this.lastSegmentId = parseInt(jobRes.data.active_segment_id)
        this.fetchSegments()
      })
  },
  methods: {
    goBack: function () {
      this.$router.push({name: 'file-list'})
    },
    fetchSegments: function () {
      let data = {
        action: 'getSegments',
        jid: this.$route.params.jobId,
        password: this.$route.params.password,
        where: 'center',
        step: 5
      }
      if (this.lastSegmentId > 0) {
        data['segment'] = this.lastSegmentId
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
              jobId: this.$route.params.jobId,
              jobPassword: this.$route.params.password
            }
          })
          if (this.lastSegmentId > 0) {
            this.setActive(this.lastSegmentId)
          } else {
            this.setActive(this.segments[0].id)
          }
        })
    },
    getContribution: function (segment) {
      const context = this.getContext(segment)
      const data = {
        action: 'getContribution',
        password: this.$route.params.password,
        is_concordance: 0,
        id_segment: segment.id,
        text: segment.original,
        id_job: this.$route.params.jobId,
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
              translation: el.translation,
              isMT: isMT
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
        id_job: this.$route.params.jobId,
        id_first_file: this.fileId,
        password: this.$route.params.password,
        status: this.activeSegment,
        translation: this.activeSegment.translation,
        segment: this.activeSegment.original,
        time_to_edit: 1,
        autosave: false,
        version: this.activeSegment.version,
        propagate: true,
        context_before: context.before,
        context_after: context.after,
        action: 'setTranslation'
      }
      SegmentsService.setTranslation(data)
        .then(() => {
          this.activeSegment.status = (status === 'translated' ? 'done' : '')
        })
    },
    setActive: function (id) {
      _.map(this.segments, e => {
        if (e.id === id) {
          e.active = true
          this.lastSegmentId = e.id
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
        let data = {
          action: 'getSegments',
          jid: this.$route.params.jobId,
          password: this.$route.params.password,
          where: activeIndex === 0 ? 'before' : 'after',
          step: 5,
          segment: this.segments[activeIndex].id
        }
        SegmentsService.getSegments(data)
          .then(r => {
            if (Object.values(r.data.data.files).length < 1) return
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
                jobId: this.$route.params.jobId,
                jobPassword: this.$route.params.password
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
          })
      }
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
    }
  }
}
</script>
