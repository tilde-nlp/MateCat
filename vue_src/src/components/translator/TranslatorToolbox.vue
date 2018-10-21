<template>
  <div class="translator-toolbox">
    <!-- GO BACK -->
    <div
      class="link-button mr-8"
      @click="goBack"
    >
      <svgicon
        class="svg-icon va-middle"
        name="arrow"
        height="24"
      />
      <div class="link ib">{{ $lang.buttons.back }}</div>
    </div>
    <!-- GO BACK END -->
    <!-- BACK -->
    <div
      v-shortkey.once="['ctrl', 'arrowup']"
      :title="$lang.tooltips.previous_segment"
      class="ta-header-button bl-blueish"
      @click="() => {$emit('toPrevious')}"
      @shortkey="() => {$emit('toPrevious')}"
    >
      <svgicon
        class="svg-icon"
        name="seg-up"
        height="24"
      />
    </div>
    <!-- BACK END -->
    <!-- FORWARD -->
    <div
      v-shortkey.once="['ctrl', 'arrowdown']"
      :title="$lang.tooltips.next_segment"
      class="ta-header-button"
      @click="() => {$emit('toNext')}"
      @shortkey="() => {$emit('toNext')}"
    >
      <svgicon
        class="svg-icon"
        name="seg-down"
        height="24"
      />
    </div>
    <!-- FORWARD END -->
    <!-- CONFIRM -->
    <div
      v-shortkey.once="['ctrl', 'enter']"
      :title="$lang.tooltips.confirm"
      class="ta-header-button bl-blueish"
      @click="() => {$emit('confirm')}"
      @shortkey="() => {$emit('confirm')}"
    >
      <svgicon
        class="svg-icon"
        name="check"
        height="24"
      />
    </div>
    <!-- CONFIRM END -->
    <!-- CANCEL -->
    <div
      v-shortkey="['ctrl', 'd']"
      :title="$lang.tooltips.delete"
      class="ta-header-button"
      @click="() => {$emit('clear')}"
      @shortkey="() => {$emit('clear')}"
    >
      <svgicon
        class="svg-icon"
        name="trash"
        height="24"
      />
    </div>
    <!-- CANCEL END -->
    <!-- COPY SOURCE TO TARGET -->
    <div
      v-shortkey.once="['ctrl', 'insert']"
      :title="$lang.tooltips.source_to_target"
      class="ta-header-button br-blueish"
      @click="() => { $emit('sourceToTarget') }"
      @shortkey="() => { $emit('sourceToTarget') }"
    >
      <svgicon
        class="svg-icon"
        name="s2t"
        height="24"
      />
    </div>
    <!-- COPY SOURCE TO TARGET END -->
    <div class="pull-right">
      <hugo-select
        :title="'Priekštulkot'"
        :icon="'pretranslate'"/>
      <!-- MT TRANSLATE -->
      <div
        class="translator-toolbox-link"
        @click="preTranslateMt()"
      >{{ $lang.buttons.translate_all_mt }}</div>
      <!-- MT TRANSLATE END -->
      <!-- TRANSLATE 100% -->
      <div
        class="translator-toolbox-link"
        @click="preTranslateTm()"
      >{{ $lang.buttons.translate_all_tm }}</div>
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
        <div class="translator-toolbox-link link">{{ $lang.buttons.original }}</div>
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
        <div class="translator-toolbox-link link">{{ $lang.buttons.translation }}</div>
      </div>
      <!-- ORIGINAL DOWNLOAD END -->
    </div>
    <div class="tt-triple-container">
      <div class="triple-block right">
        <div class="size-s mr-8"/>
        <div class="ml-32 mr-8"/>
      </div>
    </div>
  </div>
</template>
<script>
import JobsService from 'services/jobs'
import HugoSelect from 'components/HugoSelect'
export default {
  name: 'TranslatorToolbox',
  components: {
    'hugo-select': HugoSelect
  },
  props: {
    jobData: {
      type: Object,
      required: true
    },
    mtSystem: {
      type: Object,
      required: true
    }
  },
  methods: {
    downloadFile: function (link) {
      window.location.href = link
    },
    goBack: function () {
      this.$router.push({name: 'file-list'})
    },
    preTranslateTm: function () {
      this.$loading.startLoading('pretranslate')
      JobsService.preTranslate({id: this.jobData.id, password: this.jobData.password, use_tm: 1, mt_system: this.mtSystem.value})
        .then(() => {
          this.$emit('pretranslated')
        })
    },
    preTranslateMt: function () {
      this.$loading.startLoading('pretranslate')
      JobsService.preTranslate({id: this.jobData.id, password: this.jobData.password, use_mt: 1, mt_system: this.mtSystem.value})
        .then(() => {
          this.$emit('pretranslated')
        })
    }
  }
}
</script>
