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
      v-shortkey.once="['ctrl', 'insert']"
      title="Kopēt orģinālu uz tulkojumu"
      class="segment-copy-icon"
      @click="copySourceToTarget"
      @shortkey="copySourceToTarget"
    >
      <svgicon
        class="svg-icon flip-h"
        name="arrow"
        height="24"
      />
    </div>
    <div class="segment-translation-container">
      <div class="segment-translation">
        <textarea
          v-model="segment.translation"
          :disabled="segment.status === 'done'"
          class="segment-edit"
        />
      </div>
      <div class="segment-controls">
        <!-- CONFIRM -->
        <div
          v-if="segment.status !== 'done'"
          class="white-button xs x-wide w-127-i"
          title="Apstiprināt tulkojumu"
          @click="() => {$emit('setStatus', segment, 'translated')}"
        >
          <div class="white-button-icon">
            <svgicon
              class="svg-icon"
              name="check"
              height="24"
            />
          </div>
          <div class="link normal ib">Apstiprināt</div>
        </div>
        <div
          v-else
          class="disabled-white-button w-127-i"
          title="Tulkojums ir apstiprināts"
        >
          <svgicon
            class="svg-icon icon-green"
            name="check"
            height="24"
          />
          <div class="size-s ib green">Apstiprināts</div>
        </div>
        <!-- CONFIRM END -->
        <!-- DRAFT -->
        <div
          v-if="segment.status !== ''"
          class="white-button xs x-wide w-102-i"
          @click="() => {$emit('setStatus', segment, 'draft')}"
        >
          <div class="white-button-icon">
            <svgicon
              class="svg-icon"
              name="pencil"
              height="24"
            />
          </div>
          <div class="link normal ib">Rediģēt</div>
        </div>
        <div
          v-else
          class="disabled-white-button w-127-i"
          title="Tulkojums ir nepabeigts"
        >
          <svgicon
            class="svg-icon icon-yellow-darker"
            name="pencil"
            height="24"
          />
          <div class="size-s ib yellow-darker">Nepabeigts</div>
        </div>
        <!-- DRAFT END -->
        <!-- CLEAR -->
        <div
          title="Dzēst tulkojuma tekstu"
          class="white-button xs x-wide w-89-i"
          @click="() => {segment.translation = ''; $emit('setStatus', segment, 'draft')}"
        >
          <div class="white-button-icon">
            <svgicon
              class="svg-icon"
              name="close"
              height="24"
            />
          </div>
          <div class="link normal ib">Dzēst</div>
        </div>
        <!-- CLEAR END -->
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
    }
  }
}
</script>
