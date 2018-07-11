<template>
  <div class="toolbox-container">
    <div class="language-selector">
      <label
        class="input-label capitalize"
        for="fromLanguage"
      >{{ $lang.titles.from }}</label>
      <div
        id="fromLanguage"
      >
        <div
          :class="{active: fromLang === 'lv-LV'}"
          class="button languages"
          @click="setFromLang('lv-LV')"
        >{{ $lang.buttons.latvian }}</div>
        <div
          :class="{active: fromLang === 'en-US'}"
          class="button languages"
          @click="setFromLang('en-US')"
        >{{ $lang.buttons.english }}</div>
        <div
          :class="{active: fromLang === 'ru-RU'}"
          class="button languages"
          @click="setFromLang('ru-RU')"
        >{{ $lang.buttons.russian }}</div>
      </div>
    </div>
    <div class="language-selector subject">
      <div class="select-container">
        <v-select
          id="subject"
          v-model="subject"
          :options="subjects"
          name="subject"
          @input="value => {$emit('subjectChange', value)}"
        />
      </div>
    </div>
    <span
      class="icon-span mr-16"
      @click="swapLanguages()"
    >
      <svgicon
        class="svg-icon va-middle"
        name="switch"
        height="24"
      />
    </span>
    <div class="language-selector">
      <label
        class="input-label capitalize"
        for="toLanguage"
      >{{ $lang.titles.to }}</label>
      <div
        id="toLanguage"
      >
        <div
          :class="{active: toLang === 'lv-LV'}"
          class="button languages"
          @click="setToLang('lv-LV')"
        >{{ $lang.buttons.latvian }}</div>
        <div
          :class="{active: toLang === 'en-US'}"
          class="button languages"
          @click="setToLang('en-US')"
        >{{ $lang.buttons.english }}</div>
        <div
          :class="{active: toLang === 'ru-RU'}"
          class="button languages"
          @click="setToLang('ru-RU')"
        >{{ $lang.buttons.russian }}</div>
      </div>
    </div>
    <button
      :disabled="!buttonEnabled"
      :title="buttonTitle"
      class="button pull-right"
      @click="() => { if (!$loading.isLoading('translator')) $emit('translate') }"
    >
      <transition
        name="ffade"
        mode="out-in">
        <span v-if="!$loading.isLoading('translator')">{{ $lang.buttons.translate }}</span>
        <div
          v-else
          class="translate-loading-fix"
        >
          <span class="vam-helper"/>
          <img
            :src="$assetPath + 'loading-spinner.svg'"
            class="va-middle"
            height="24"
          >
        </div>
      </transition>
    </button>
  </div>
</template>

<script>
// ru-RU en-US lv-LV
import LanguageService from 'services/languages'
import _ from 'lodash'
export default {
  name: 'FileListToolbar',
  props: {
    buttonEnabled: {
      type: Boolean,
      required: true
    }
  },
  data: function () {
    return {
      languages: [],
      fromLang: '',
      toLang: '',
      defaultFromCode: 'en-US',
      defaultToCode: 'lv-LV',
      subjects: [],
      subject: null,
      defaultSubjectKey: 'general'
    }
  },
  computed: {
    buttonTitle: function () {
      return this.buttonEnabled ? 'Augšupielādēt failus' : 'Vispirms jāievelk faili'
    }
  },
  mounted: function () {
    this.setFromLang(this.defaultToCode)
    this.setToLang(this.defaultFromCode)
    LanguageService.getSubjectsList()
      .then(r => {
        // Get relevant data for subjects dropdown
        this.subjects = _.map(r.data.subjects, el => {
          return {
            label: el.display,
            value: el.key
          }
        })
        // Set default subject
        this.subject = _.find(this.subjects, { value: this.defaultSubjectKey })
      })
  },
  methods: {
    swapLanguages: function () {
      const oldFromLanguage = this.fromLang
      this.fromLang = this.toLang
      this.toLang = oldFromLanguage
    },
    setFromLang: function (code) {
      this.fromLang = code
      this.$emit('fromLangChange', code)
    },
    setToLang: function (code) {
      this.toLang = code
      this.$emit('toLangChange', code)
    }
  }
}
</script>
