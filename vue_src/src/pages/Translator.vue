<template>
  <div class="page-container">
    <button
      class="button mb-16"
      @click="goBack"
    >Atpakaļ</button>
    <div class="clear" />
    <div class="translator-container">
      <translator-toolbox />
      <div class="segments-container">
        <translator-segment
          v-for="(segment, index) in segments"
          :key="index"
          :index="index"
          :segment-data="segment"
          @click="setActive"
          @done="done"
        />
      </div>
    </div>
    <translator-assistant />
  </div>
</template>

<script>
import _ from 'lodash'
import SegmentsService from 'services/segments'
import TranslatorToolbox from 'components/translator/TranslatorToolbox'
import TranslatorSegment from 'components/translator/TranslatorSegment'
import TranslatorAssistant from 'components/translator/TranslatorAssistant'
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
      fileId: ''
    }
  },
  mounted: function () {
    const data = {
      action: 'getSegments',
      jid: this.$route.params.jobId,
      password: this.$route.params.password,
      where: 'center',
      step: 10
    }
    SegmentsService.getSegments(data)
      .then(r => {
        this.fileId = Object.keys(r.data.data.files)[0]
        this.segments = _.map(Object.values(r.data.data.files)[0].segments, el => {
          return {
            id: el.sid,
            original: el.segment,
            translation: el.translation,
            status: (el.status === 'TRANSLATED' ? 'done' : ''),
            active: false,
            version: el.version
          }
        })
        this.segments.forEach(item => {
          if (item.status === '') this.getContribution(item)
        })
      })
  },
  methods: {
    goBack: function () {
      this.$router.push({name: 'file-list'})
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
        num_results: 3,
        context_before: context.before,
        context_after: context.after
      }
      SegmentsService.getContribution(data)
        .then(r => {
          const match = r.data.data.matches[0]
          if (typeof (match) !== 'undefined') segment.translation = match.translation
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
    done: function (segment) {
      const context = this.getContext(segment)
      const data = {
        id_segment: segment.id,
        id_job: this.$route.params.jobId,
        id_first_file: this.fileId,
        password: this.$route.params.password,
        status: 'translated',
        translation: segment.translation,
        segment: segment.original,
        time_to_edit: 1,
        autosave: false,
        version: segment.version,
        propagate: true,
        context_before: context.before,
        context_after: context.after,
        action: 'setTranslation'
      }
      SegmentsService.setTranslation(data)
        .then(() => {
          segment.status = 'done'
        })
    },
    setActive: function (id) {
      _.map(this.segments, e => {
        e.active = e.id === id
        return e
      })
    }
  }
}
</script>
