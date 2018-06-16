<template>
  <div
    :class="{active: segment.active}"
    class="segment-container"
    @click="() => {$emit('click', segment.id)}"
  >
    <div class="w-512 bg-grey-light b-blueish p-8 border-box ib">
      {{ segment.original }}
    </div>
    <div
      title="Kopēt orģinālu uz tulkojumu"
      class="segment-copy-icon"
      @click="copySourceToTarget"
    >
      <svgicon
        class="svg-icon flip-h"
        name="arrow"
        height="24"
      />
    </div>
    <div class="segment-translation-container">
      <div class="segment-translation">{{ segment.translation }}</div>
      <div class="segment-controls">
        <!-- CONFIRM -->
        <div class="white-button xs x-wide w-127-i">
          <div class="white-button-icon">
            <svgicon
              class="svg-icon"
              name="check"
              height="24"
            />
          </div>
          <div class="link normal ib">Apstiprināt</div>
        </div>
        <!-- CONFIRM END -->
        <!-- DRAFT -->
        <div class="white-button xs x-wide w-102-i">
          <div class="white-button-icon">
            <svgicon
              class="svg-icon"
              name="pencil"
              height="24"
            />
          </div>
          <div class="link normal ib">Rediģēt</div>
        </div>
        <!-- DRAFT END -->
        <!-- CONFIRM -->
        <div class="white-button xs x-wide w-89-i">
          <div class="white-button-icon">
            <svgicon
              class="svg-icon"
              name="close"
              height="24"
            />
          </div>
          <div class="link normal ib">Dzēst</div>
        </div>
        <!-- CONFIRM END -->
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
    copySourceToTarget: function () {
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
