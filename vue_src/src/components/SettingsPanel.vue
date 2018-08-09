<template>
  <div class="settings-container">
    <div class="settings-container-head">
      {{ $lang.titles.settings }}
      <button
        class="close-button"
        @click="() => { $emit('closeSettings') }"
      >{{ $lang.buttons.close }}</button>
    </div>
    <div class="bb-blueish"/>
    <div class="settings-container-options">
      <div>
        <check-box/>
        <div class="ib va-top">
          {{ $lang.messages.fill_100p_tm }}
        </div>
      </div>
      <div class="mt-24">
        <check-box/>
        <div class="ib va-top">
          {{ $lang.messages.fill_mt }}
        </div>
      </div>
      <div class="input-label mt-32 size-s-i">
        {{ $lang.titles.translation_memories }}
      </div>
      <div class="input-label ib-i w-64">
        {{ $lang.titles.read }}
      </div>
      <div class="input-label ib-i w-64">
        {{ $lang.titles.write }}
      </div>
      <div class="mt-8">
        <svgicon
          v-if="$loading.isLoading('memories')"
          class="svg-loading va-middle"
          name="loading"
          height="24"
        />
        <transition-group
          v-else
          name="ffade"
          mode="out-in"
        >
          <div
            v-for="(memory, index) in memories"
            :key="index"
          >
            <div class="size-s dark b-light-darker mt-4 p-8">
              <check-box
                :value="memory.read"
                class="ib w-64"
                @change="val => { setRead(memory, val) }"
              />
              <check-box
                :value="memory.write"
                :disabled="!memory.canUpdate"
                class="ib w-64"
                @change="val => { setWrite(memory, val) }"
              />
              <div class="ib va-top">{{ memory.name }}</div>
            </div>
          </div>
        </transition-group>
      </div>
    </div>
  </div>
</template>
<script>
import CheckBox from './Checkbox'
import TranslationMemoryService from 'services/translation-memory'
import Vue from 'vue'
export default {
  name: 'SettingsPanel',
  components: {
    'check-box': CheckBox
  },
  data: function () {
    return {
      memories: []
    }
  },
  mounted: function () {
    this.$loading.startLoading('memories')
    TranslationMemoryService.get()
      .then(response => {
        this.memories = null
        this.memories = response.data
        this.$loading.endLoading('memories')
      })
  },
  methods: {
    setRead: function (memory, value) {
      Vue.set(memory, 'read', value)
      TranslationMemoryService.saveSettings(memory)
    },
    setWrite: function (memory, value) {
      Vue.set(memory, 'write', value)
      TranslationMemoryService.saveSettings(memory)
    }
  }
}
</script>
