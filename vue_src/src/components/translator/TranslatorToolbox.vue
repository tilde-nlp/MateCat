<template>
  <div>
    <div class="mt-16">
      <!-- UNDO -->
      <div
        title="Undo: Ctrl + Z"
        class="white-button xs ml-8">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon"
            name="undo"
            height="24"
          />
        </div>
      </div>
      <!-- UNDO END -->
      <!-- REDO -->
      <div
        title="Redo: Ctrl + R"
        class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon flip-h"
            name="undo"
            height="24"
          />
        </div>
      </div>
      <!-- REDO END -->
      <!-- CONFIRM -->
      <div
        v-shortkey.once="['ctrl', 'enter']"
        title="Confirm: Ctrl + Enter"
        class="white-button xs mr-32-i"
        @click="() => {$emit('confirm')}"
        @shortkey="() => {$emit('confirm')}"
      >
        <div class="white-button-icon">
          <svgicon
            class="svg-icon"
            name="check"
            height="24"
          />
        </div>
      </div>
      <!-- CONFIRM END -->
      <!-- SPLIT -->
      <div
        title="Split: Ctrl + S"
        class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon"
            name="split"
            height="24"
          />
        </div>
      </div>
      <!-- SPLIT END -->
      <!-- JOIN -->
      <div
        title="Join: Ctrl + J"
        class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon"
            name="join"
            height="24"
          />
        </div>
      </div>
      <!-- JOIN END -->
      <!-- CANCEL -->
      <div
        title="Delete: Ctrl + D"
        class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon"
            name="trash"
            height="24"
          />
        </div>
      </div>
      <!-- CANCEL END -->
      <!-- FORWARD -->
      <div
        title="Copy source to target: Ctrl + Insert"
        class="white-button xs mr-32-i">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon flip-h"
            name="arrow"
            height="24"
          />
        </div>
      </div>
      <!-- FORWARD END -->
      <!-- BACK -->
      <div
        title="Previous unconfirmed segment: Ctrl + Up"
        class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon flip-h"
            name="chevron"
            height="24"
          />
        </div>
      </div>
      <!-- BACK END -->
      <!-- FORWARD? -->
      <div
        title="Next unconfirmed segment: Ctrl + Down"
        class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon"
            name="chevron"
            height="24"
          />
        </div>
      </div>
      <!-- FORWARD? END -->
      <div class="pull-right">
        <!-- MT TRANSLATE -->
        <div class="translator-toolbox-link">Tulkot visu ar MT</div>
        <!-- MT TRANSLATE END -->
        <!-- TRANSLATE 100% -->
        <div class="translator-toolbox-link">Tulkot visu 100%</div>
        <!-- TRANSLATE 100% END -->
        <!-- ORIGINAL DOWNLOAD -->
        <div
          class="tt-icon-link-container"
          @click="downloadFile(jobData.originalUrl)"
        >
          <div class="icon-container">
            <svgicon
              class="svg-icon va-middle"
              name="download"
              height="24"
            />
          </div>
          <div class="translator-toolbox-link link">Orģināls</div>
        </div>
        <!-- ORIGINAL DOWNLOAD END -->
        <!-- ORIGINAL DOWNLOAD -->
        <div
          class="tt-icon-link-container"
          @click="downloadFile(jobData.translatedUrl)"
        >
          <div class="icon-container">
            <svgicon
              class="svg-icon va-middle"
              name="download"
              height="24"
            />
          </div>
          <div class="translator-toolbox-link link">Tulkojums</div>
        </div>
        <!-- ORIGINAL DOWNLOAD END -->
      </div>
    </div>
    <div class="tt-triple-container">
      <div class="triple-block">
        <div class="segment-search-container">
          <svgicon
            class="svg-icon icon-blueish-darker-still placeholder"
            name="search"
            height="24"
          />
          <input
            class="input w-100p w-placeholder"
            type="text"
            placeholder="Atlasīt no sākotnējā teksta"
          >
        </div>
      </div>
      <div class="triple-block">
        <div class="segment-search-container">
          <svgicon
            class="svg-icon icon-blueish-darker-still placeholder"
            name="search"
            height="24"
          />
          <input
            class="input w-100p w-placeholder"
            type="text"
            placeholder="Atlasīt no tulkotā teksta"
          >
        </div>
      </div>
      <div class="triple-block right">
        <div class="size-s mr-8">Iztulkoti {{ jobData.progress }}% no {{ jobData.segments }} segmentiem</div>
        <div class="ml-32 mr-8">
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
    </div>
  </div>
</template>
<script>
export default {
  name: 'TranslatorToolbox',
  props: {
    jobData: {
      type: Object,
      required: true
    }
  },
  methods: {
    downloadFile: function (link) {
      window.location.href = link
    }
  }
}
</script>
