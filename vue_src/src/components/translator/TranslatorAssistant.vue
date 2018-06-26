<template>
  <div class="triple-block h-100p">
    <div class="segment-col assistant">
      <div class="tabber">
        <div
          :class="{active: activeTab === 'translate'}"
          class="tabber-section"
          @click="activeTab = 'translate'"
        >
          Tulkojumi
        </div>
        <div
          :class="{active: activeTab === 'hotkeys'}"
          class="tabber-section"
          @click="activeTab = 'hotkeys'"
        >
          Taustiņkombinācijas
        </div>
      </div>
      <transition
        name="ffade"
        mode="out-in">
        <div
          v-if="activeTab === 'translate'"
          class="tab">
          <label
            class="input-label"
            for="mt"
          >Mašīntulks</label>
          <div class="select-container">
            <v-select
              id="mt"
              v-model="system"
              :options="systems"
              name="mt"
              @input="value => {$emit('mtSystemChange', value)}"
            />
          </div>
          <div class="mt-24">
            <label class="input-label">Ieteikumi</label>
            <img
              v-if="!activeSegment.suggestionsLoaded"
              :src="$assetPath + 'loading.svg'"
              class="splash-image"
              height="48"
            >
            <div
              v-else-if="activeSegment.suggestions.length < 1">
              Nav ieteikumu
            </div>
            <div
              v-else
              :style="{'max-height': maxHeight + 'px'}"
              class="suggestions-container"
            >
              <transition-group
                name="ffade"
                mode="out-in"
              >
                <div
                  v-shortkey="['ctrl', parseInt(index + 1)]"
                  v-for="(suggestion, index) in activeSegment.suggestions"
                  :key="index"
                  class="suggestion"
                  @click="() => { activeSegment.translation = suggestion.translation }"
                  @shortkey="() => { activeSegment.translation = suggestion.translation }"
                >
                  <div class="suggestion-nr">{{ index + 1 }}</div>
                  <div
                    :class="{'high-match': suggestion.rawMatch > 69, 'mid-match': suggestion.rawMatch > 49 && suggestion.rawMatch < 70, 'mt-match': suggestion.isMT}"
                    class="suggestion-text"
                  >
                    <span v-if="suggestion.isMT">{{ suggestion.translation }}</span>
                    <div v-else>
                      <div>
                        <div class="ib mr-24">Izveidoja: <div class="input-label ib-i">{{ suggestion.createdBy }}</div></div>
                        <div class="ib">Lietošanas biežums: <div class="input-label ib-i">{{ suggestion.usageCount }}</div></div>
                      </div>
                      <div class="input-label mb-4-i">Orģināls</div>
                      <div class="mb-8 bb-light-darker">{{ suggestion.segment }}</div>
                      <div class="input-label mb-4-i">Tulkojums</div>
                      <div>{{ suggestion.translation }}</div>
                    </div>
                  </div>
                  <div
                    :class="{'high-match': suggestion.rawMatch > 69, 'mid-match': suggestion.rawMatch > 49 && suggestion.rawMatch < 70, 'mt-match': suggestion.isMT}"
                    class="suggestion-match"
                  >{{ suggestion.match }}</div>
                </div>
              </transition-group>
            </div>
          </div>
        </div>
      </transition>
    </div>
    <div class="number-col">
      &nbsp;
    </div>
  </div>
</template>
<script>
import LanguagesService from 'services/languages.js'
import _ from 'lodash'
export default {
  name: 'TranslatorAssistant',
  props: {
    activeSegment: {
      type: Object,
      required: true
    },
    maxHeight: {
      type: Number,
      required: true
    }
  },
  data: function () {
    return {
      activeTab: 'translate',
      systems: [],
      system: null
    }
  },
  mounted: function () {
    LanguagesService.getMTSystems()
      .then(langsRes => {
        this.systems = _.map(langsRes.data, el => {
          return {
            label: el.Title.Text,
            value: el.ID
          }
        })
        this.system = this.systems[0]
        this.$emit('mtSystemChange', this.system)
      })
  }
}
</script>
