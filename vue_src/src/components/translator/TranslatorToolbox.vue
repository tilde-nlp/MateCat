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
        :title="$lang.titles.pretranslate"
        :icon="'pretranslate'"
        :options="pretranslateOptions"
        @select="pretranslate"
      />
      <hugo-select
        :title="$lang.titles.download"
        :icon="'download'"
        :options="downloadOptions"
        @select="download"
      />
      <div
        class="icon-span mr-24"
        @click="() => { $emit('toggleSettings') }"
      >
        <svgicon
          class="svg-icon va-middle"
          name="cog"
          height="24"
        />
        <div
          class="link ib">{{ $lang.buttons.settings }}</div>
      </div>
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
  computed: {
    pretranslateOptions: function () {
      return [
        {id: 'mt', value: this.$lang.buttons.translate_all_mt},
        {id: 'tm', value: this.$lang.buttons.translate_all_tm}
      ]
    },
    downloadOptions: function () {
      return [
        {id: 'original', value: this.$lang.buttons.original},
        {id: 'translation', value: this.$lang.buttons.translation}
      ]
    }
  },
  methods: {
    downloadFile: function (link) {
      window.location.href = link
    },
    goBack: function () {
      this.$router.push({name: 'file-list'})
    },
    pretranslate: function (id) {
      if (id === 'mt') {
        this.preTranslateMt()
      } else {
        this.preTranslateTm()
      }
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
    },
    download: function (id) {
      if (id === 'original') {
        this.downloadFile(this.jobData.originalUrl)
      } else {
        this.downloadFile(this.jobData.translatedUrl)
      }
    }
  }
}
</script>
