<template>
  <div class="toolbox-container">
    <label
      class="input-label capitalize"
      for="fromLanguage"
    >{{ $lang.titles.from }}</label>
    <div class="language-selector">
      <div
        id="fromLanguage"
      >
        <div
          :class="{active: fromLang === 'en-US'}"
          class="button languages bl br"
          @click="setFromLang('en-US')"
        >{{ $lang.buttons.english }}</div>
        <div
          :class="{active: fromLang === 'lv-LV'}"
          class="button languages"
          @click="setFromLang('lv-LV')"
        >{{ $lang.buttons.latvian }}</div>
        <div
          :class="{active: fromLang === 'ru-RU'}"
          class="button languages bl br"
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
          class="w-128-i"
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
        class="input-label capitalize file-toolbox"
        for="toLanguage"
      >{{ $lang.titles.to }}</label>
      <div
        id="toLanguage"
      >
        <div
          v-if="fromLang === 'lv-LV'"
          :class="{active: toLang === 'ru-RU'}"
          class="button languages bl br"
          @click="setToLang('ru-RU')"
        >{{ $lang.buttons.russian }}</div>
        <div
          v-if="fromLang === 'lv-LV'"
          :class="{active: toLang === 'en-US'}"
          class="button languages br"
          @click="setToLang('en-US')"
        >{{ $lang.buttons.english }}</div>
        <div
          v-if="fromLang !== 'lv-LV'"
          :class="{active: toLang === 'lv-LV'}"
          class="button languages bl br"
          @click="setToLang('lv-LV')"
        >{{ $lang.buttons.latvian }}</div>
      </div>
    </div>
    <div class="pull-right">
      <div
        class="icon-span mr-24"
        @click="translate(key)"
      >
        <svgicon
          class="svg-icon va-middle"
          name="cog"
          height="24"
        />
        <div class="link ib">{{ $lang.buttons.settings }}</div>
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
          <span v-if="!$loading.isLoading('translator')">{{ $lang.buttons.analyze }}</span>
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
  </div>
</template>

<script>
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
    this.reloadSystem()
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
      if (this.fromLang === 'en-US' || this.fromLang === 'ru-RU') {
        this.setToLang('lv-LV')
      } else if (this.toLang === 'lv-LV') {
        this.setToLang('en-US')
      }
      this.reloadSystem()
    },
    setToLang: function (code) {
      this.toLang = code
      this.$emit('toLangChange', code)
      this.reloadSystem()
    },
    reloadSystem: function () {
      LanguageService.getSubjectsList(this.$lang.getLang())
        .then(r => {
          // Get relevant data for subjects dropdown
          const filteredSystems = _.filter(r.data.System, el => {
            return el.SourceLanguage.Code === this.fromLang.substring(0, 2) && el.TargetLanguage.Code === this.toLang.substring(0, 2)
          })
          this.subjects = _.map(filteredSystems, el => {
            return {
              label: el.Domain,
              value: el.ID
            }
          })
          // Set default subject
          this.subject = this.subjects[0]
        })
    }
  }
}
</script>
