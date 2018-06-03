<template>
  <div class="page-container">
    <button
      class="button"
      @click="goBack"
    >Atpakaļ</button>
    <div class="translator-container">
      <div class="section">
        Some tools and stuff
      </div>
      <div
        v-for="(segment, index) in segments"
        :key="index"
        :class="{active: segment.active}"
        class="section"
        @click="activateSegment(segment)"
      >
        <div class=" column number">{{ index + 1 }}</div>
        <textarea
          v-model="segment.original"
          class="column original"
          disabled
        />
        <div
          class="column divider"
          @click="translate(segment)"
        >></div>
        <textarea
          v-model="segment.translation"
          class="column translation"
        />
        <div class="column controls">
          <div
            class="icon-container"
            @click="done(segment)">
            <svgicon
              :class="{active: segment.status === 'done'}"
              class="svg-icon"
              name="check"
              height="30"
            />
          </div>
          <div
            class="icon-container"
            @click="incomplete(segment)">
            <svgicon
              :class="{active: segment.status === 'draft'}"
              class="svg-icon"
              name="question"
              height="30"
            />
          </div>
          <div
            class="icon-container"
            @click="trash(segment)">
            <svgicon
              class="svg-icon"
              name="delete"
              height="30"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from 'lodash'
import SegmentsService from '../axios/segments'
export default {
  name: 'Translator',
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
    activateSegment: function (segment) {
      _.map(this.segments, e => {
        e.active = e.id === segment.id
        return e
      })
    },
    translate: function (segment) {
      segment.translation = segment.original
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
        .then(r => {
          console.log(r)
          segment.status = 'done'
        })
    },
    incomplete: function (segment) {
      segment.status = 'draft'
    },
    trash: function (segment) {
      segment.translation = ''
    },
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
    }
  }
}
</script>

<style lang="less" scoped>
  @import (reference) "~less-entry";
  @section-height: @spacer-128;
  .section {
    background-color: @color-light-darker;
    transition: all unit(@golden / 10, s) ease-in-out;
    &.active {
      background-color: @color-light;
    }
    .mt-8;
    .mb-8;
    height: @section-height;
    line-height: @section-height;
    .column {
      display: inline-block;
      vertical-align: middle;
      .border-box;
      height: @section-height;
      &.number {
        .size-s;
        line-height: @section-height;
        border-right: solid 2px @color-white;
        .pl-4;
        .pr-4;
      }
      &.original {
        .size-s;
        .w-512;
      }
      &.divider {
        .size-m;
        font-weight: bolder;
        line-height: @section-height;
        cursor: pointer;
      }
      &.translation {
        .size-s;
        .w-512;
      }
      &.controls {
        .icon-container {
          height: 30px;
          line-height: 30px;
        }
      }
    }
  }
</style>
