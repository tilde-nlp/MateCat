<template>
  <div class="translator-assistant">
    <div class="assistant-menu">
      <div
        :class="{active: activeTab === 'translate'}"
        class="assistant-nav"
        @click="activeTab = 'translate'"
      >Tulkojumi</div>
      <div
        :class="{active: activeTab === 'shortcuts'}"
        class="assistant-nav"
        @click="activeTab = 'shortcuts'"
      >Taustiņkombinācijas</div>
    </div>
    <transition
      name="fade"
      mode="out-in"
    >
      <div
        v-if="activeTab === 'translate'"
        :key="1"
      >
        <div class="mb-8">
          <div class="ib mr-16 w-192">Izvēlēties mašīntulku</div>
          <select class="ib w-128">
            <option>1</option>
          </select>
        </div>
        <div class="mb-8">
          <div class="ib mr-16 w-192">Izvēlēties tulkošanas atmiņu</div>
          <select class="ib w-128">
            <option>1</option>
          </select>
        </div>
        <div class="mb-8">
          <div class="mb-4">Ieteikumi</div>
          <div class="b-grey">
            <div class="ib">1</div>
            <div class="ib">Some text bla bla bla</div>
            <div class="ib">100%</div>
          </div>
          <div class="b-grey">
            <div class="ib">1</div>
            <div class="ib">Some text bla bla bla</div>
            <div class="ib">96%</div>
          </div>
        </div>
        <div class="mb-8">
          <div>Terminu vārdnīca</div>
          <div class="b-grey">
            <div class="ib">1</div>
            <div class="ib">bla bla bla</div>
            <div class="ib">blue blue blue</div>
          </div>
          <div class="b-grey">
            <div class="ib">1</div>
            <div class="ib">bla bla bla</div>
            <div class="ib">blue blue blue</div>
          </div>
        </div>
        <div>
          <div class="mb-4">Meklēt atmiņā</div>
          <div>
            <input
              type="text"
              class="translator-input"
              placeholder="Rakstīt tekstu"
            >
          </div>
        </div>
      </div>
      <div
        v-else
        :key="2"
      >
        <div class="shortcut-row">
          <div class="shortcut-buttons">
            <div class="shortcut-button">Enter</div>
          </div>
          <div class="shortcut-info">
            Autocomplete with highlighted word
          </div>
        </div>
        <div class="shortcut-row">
          <div class="shortcut-buttons">
            <div class="shortcut-button">Shift</div>
            +
            <div class="shortcut-button">Enter</div>
          </div>
          <div class="shortcut-info">
            Autocomplete with full suggestion
          </div>
        </div>
        <div class="shortcut-row">
          <div class="shortcut-buttons">
            <div class="shortcut-button">Control</div>
            +
            <div class="shortcut-button">Space</div>
          </div>
          <div class="shortcut-info">
            Select next word
          </div>
        </div>
        <div class="shortcut-row">
          <div class="shortcut-buttons">
            <div class="shortcut-button">Control</div>
            +
            <div class="shortcut-button">Enter</div>
          </div>
          <div class="shortcut-info">
            Confirm (save) segment
          </div>
        </div>
        <div class="shortcut-row">
          <div class="shortcut-buttons">
            <div class="shortcut-button">Control</div>
            +
            <div class="shortcut-button">Up</div>
          </div>
          <div class="shortcut-info">
            Go to previous unconfirmed segment
          </div>
        </div>
        <div class="shortcut-row">
          <div class="shortcut-buttons">
            <div class="shortcut-button">Control</div>
            +
            <div class="shortcut-button">Down</div>
          </div>
          <div class="shortcut-info">
            Go to next unconfirmed segment
          </div>
        </div>
        <div class="shortcut-row">
          <div class="shortcut-buttons">
            <div class="shortcut-button">Control</div>
            +
            <div class="shortcut-button">Insert</div>
          </div>
          <div class="shortcut-info">
            Copy source to target
          </div>
        </div>
        <div class="shortcut-row">
          <div class="shortcut-buttons">
            <div class="shortcut-button">Control</div>
            +
            <div class="shortcut-button">Z</div>
          </div>
          <div class="shortcut-info">
            Undo last change in editor
          </div>
        </div>
        <div class="shortcut-row">
          <div class="shortcut-buttons">
            <div class="shortcut-button">Alt</div>
            +
            <div class="shortcut-button">T</div>
          </div>
          <div class="shortcut-info">
            Open lexicon with selected text
          </div>
        </div>
        <div class="shortcut-row">
          <div class="shortcut-buttons">
            <div class="shortcut-button">F7</div>
          </div>
          <div class="shortcut-info">
            Run QA check
          </div>
        </div>
        <div class="shortcut-row">
          <div class="shortcut-buttons">
            <div class="shortcut-button">Esc</div>
          </div>
          <div class="shortcut-info">
            Close TM diff view
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>
<script>
export default {
  name: 'TranslatorAssistant',
  data: function () {
    return {
      activeTab: 'translate'
    }
  }
}
</script>
