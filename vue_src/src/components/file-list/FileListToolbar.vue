<template>
  <div class="toolbox-container">
    <div class="language-selector">
      <label
        class="input-label"
        for="fromLanguage"
      >No</label>
      <div class="select-container">
        <v-select
          id="fromLanguage"
          v-model="fromLang"
          :options="languages"
          name="fromLanguage"
          @input="value => {$emit('fromLangChange', value)}"
        />
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
        class="input-label"
        for="toLanguage"
      >Uz</label>
      <div class="select-container">
        <v-select
          id="toLanguage"
          v-model="toLang"
          :options="languages"
          name="fromLanguage"
          @input="value => {$emit('toLangChange', value)}"
        />
      </div>
    </div>
  </div>
</template>

<script>
import LanguageService from 'services/languages'
import _ from 'lodash'
export default {
  name: 'FileListToolbar',
  data: function () {
    return {
      languages: [],
      fromLang: null,
      toLang: null,
      defaultFromCode: 'en-US',
      defaultToCode: 'fr-FR',
      subjects: [],
      subject: null,
      defaultSubjectKey: 'general'
    }
  },
  mounted: function () {
    LanguageService.getList()
      .then(r => {
        // Get relevant data for languages dropdown
        this.languages = _.map(r.data.languages, el => {
          return {
            label: el.name,
            value: el.code
          }
        })
        // Set default languages
        this.fromLang = _.find(this.languages, { value: this.defaultFromCode })
        this.toLang = _.find(this.languages, { value: this.defaultToCode })
      })
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
    }
  }
}
</script>
