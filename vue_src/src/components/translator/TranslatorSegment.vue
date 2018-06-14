<template>
  <div class="translator-section segment-section">
    <div
      class="section"
      @click="() => {$emit('click', segment.id)}"
    >
      <div class="column number">{{ index + 1 }}</div>
      <div
        :class="{active: segment.active}"
        class="column content"
      >
        <div class="column translation">
          <div class="tags-row" />
          <textarea
            v-model="segment.original"
            disabled
          />
        </div>
        <div
          class="column divider icon-container"
          @click="translate()"
        ><svgicon
          class="svg-icon va-middle"
          name="chevron"
          height="32"
        /></div>
        <div class="column translation">
          <div class="tags-row" />
          <textarea
            v-model="segment.translation"
          />
        </div>
        <div class="column controls">
          <div
            class="icon-container"
            @click="() => {$emit('done', segment)}">
            <svgicon
              :class="{active: segment.status === 'done'}"
              class="svg-icon icon-green"
              name="check-circle"
              height="30"
            />
          </div>
          <div
            class="icon-container"
            @click="incomplete()">
            <svgicon
              :class="{active: segment.status === 'draft'}"
              class="svg-icon icon-yellow"
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
