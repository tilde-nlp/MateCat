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
          @input="onSystemChange"
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
  </div>
</template>

<script>
import LanguageService from 'services/languages'
export default {
  name: 'FileListToolbar',
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
      this.$loading.startLoading('mt-systems')
      LanguageService.getSubjectsList(this.$lang.getLang())
        .then(r => {
          this.subjects = LanguageService.filterSystems(r.data.System, this.fromLang.substring(0, 2), this.toLang.substring(0, 2))
          for (let i = 0; i < this.subjects.length; i++) {
            this.subjects[i].label = this.$lang.titles[this.subjects[i].label]
          }
          this.subject = this.subjects[0]
          this.$loading.endLoading('mt-systems')
        })
        .catch(() => {
          this.$loading.endLoading('mt-systems')
        })
    },
    onSystemChange: function (value) {
      this.$emit('subjectChange', value)
      this.$store.commit('mtSystem', value.value)
    }
  }
}
</script>
