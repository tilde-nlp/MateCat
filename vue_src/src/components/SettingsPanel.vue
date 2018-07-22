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
      <div class="checkbox-container">
        <input type="checkbox">
        <span class="checkmark"/>
        {{ $lang.messages.fill_100p_tm }}
      </div>
      <div class="checkbox-container mt-24">
        <input type="checkbox">
        <span class="checkmark"/>
        {{ $lang.messages.fill_mt }}
      </div>
    </div>
  </div>
</template>
<script>
export default {
  name: 'SettingsPanel'
}
</script>
