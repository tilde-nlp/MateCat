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
              @input="value => {$emit('mtChange', value)}"
            />
          </div>
          <div>
            <label
              class="input-label"
            >Ieteikumi</label>
            <div class="suggestion">
              <div class="suggestion-nr">1</div>
              <div class="suggestion-text">
                It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.
              </div>
              <div class="suggestion-match">50%</div>
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
      })
  }
}
</script>
