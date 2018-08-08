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
        <div
          v-for="(memory, index) in memories"
          :key="index"
        >
          <div class="size-s dark b-light-darker mt-4 p-8">
            <check-box
              class="ib w-64"
            />
            <check-box
              class="ib w-64"
            />
            <div class="ib va-top">{{ memory.name }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import CheckBox from './Checkbox'
import TranslationMemoryService from 'services/translation-memory'
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
    TranslationMemoryService.get()
      .then(response => {
        this.memories = null
        this.memories = response.data
      })
  }
}
</script>
