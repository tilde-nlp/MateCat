<template>
  <div class="translator-section pl-0-i">
    <div
      :class="{active: segment.active}"
      class="section"
      @click="() => {$emit('click', segment.id)}"
    >
      <div class="column number">{{ index + 1 }}</div>
      <textarea
        v-model="segment.original"
        class="column original"
        disabled
      />
      <div
        class="column divider icon-container"
        @click="translate()"
      ><svgicon
        class="svg-icon va-middle"
        name="chevron"
        height="32"
      /></div>
      <textarea
        v-model="segment.translation"
        class="column translation"
      />
      <div class="column controls">
        <div
          class="icon-container"
          @click="() => {$emit('done', segment)}">
          <svgicon
            :class="{active: segment.status === 'done'}"
            class="svg-icon"
            name="check-circle"
            height="30"
          />
        </div>
        <div
          class="icon-container"
          @click="incomplete()">
          <svgicon
            :class="{active: segment.status === 'draft'}"
            class="svg-icon"
            name="question"
            height="30"
          />
        </div>
        <div
          class="icon-container"
          @click="trash()">
          <svgicon
            class="svg-icon"
            name="delete"
            height="30"
          />
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  name: 'TranslatorSegment',
  props: {
    segmentData: {
      type: Object,
      required: true
    },
    index: {
      type: Number,
      required: true
    }
  },
  data: function () {
    return {
      segment: {}
    }
  },
  mounted: function () {
    this.segment = this.segmentData
  },
  methods: {
    translate: function () {
      this.segment.translation = this.segment.original
    },
    incomplete: function () {
      this.segment.status = 'draft'
    },
    trash: function () {
      this.segment.translation = ''
    }
  }
}
</script>
<style lang="less" scoped>
  @import (reference) "~less-entry";
  @section-height: @spacer-128;
  .section {
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
        .size-m;
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
          display: block;
          margin-bottom: @spacer-8;
        }
      }
    }
  }
</style>
