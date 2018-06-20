<template>
  <div>
    <div class="mt-16">
      <!-- UNDO -->
      <div
        title="Undo: Ctrl + Z"
        class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon"
            name="undo"
            height="24"
          />
        </div>
      </div>
      <!-- UNDO END -->
      <!-- REDO -->
      <div
        title="Redo: Ctrl + R"
        class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon flip-h"
            name="undo"
            height="24"
          />
        </div>
      </div>
      <!-- REDO END -->
      <!-- CONFIRM -->
      <div
        v-shortkey.once="['ctrl', 'enter']"
        title="Confirm: Ctrl + Enter"
        class="white-button xs mr-32-i"
        @click="() => {$emit('confirm')}"
        @shortkey="() => {$emit('confirm')}"
      >
        <div class="white-button-icon">
          <svgicon
            class="svg-icon"
            name="check"
            height="24"
          />
        </div>
      </div>
      <!-- CONFIRM END -->
      <!-- SPLIT -->
      <div
        title="Split: Ctrl + S"
        class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon"
            name="split"
            height="24"
          />
        </div>
      </div>
      <!-- SPLIT END -->
      <!-- JOIN -->
      <div
        title="Join: Ctrl + J"
        class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon"
            name="join"
            height="24"
          />
        </div>
      </div>
      <!-- JOIN END -->
      <!-- CANCEL -->
      <div
        title="Delete: Ctrl + D"
        class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon"
            name="trash"
            height="24"
          />
        </div>
      </div>
      <!-- CANCEL END -->
      <!-- FORWARD -->
      <div
        title="Copy source to target: Ctrl + Insert"
        class="white-button xs mr-32-i">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon flip-h"
            name="arrow"
            height="24"
          />
        </div>
      </div>
      <!-- FORWARD END -->
      <!-- BACK -->
      <div
        title="Previous unconfirmed segment: Ctrl + Up"
        class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon flip-h"
            name="chevron"
            height="24"
          />
        </div>
      </div>
      <!-- BACK END -->
      <!-- FORWARD? -->
      <div
        title="Next unconfirmed segment: Ctrl + Down"
        class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon"
            name="chevron"
            height="24"
          />
        </div>
      </div>
      <!-- FORWARD? END -->
      <!-- TEXT? -->
      <div class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon"
            name="text"
            height="24"
          />
        </div>
      </div>
      <!-- TEXT? END -->
      <!-- T -->
      <div class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon"
            name="font"
            height="24"
          />
        </div>
      </div>
      <!-- T END -->
      <!-- MT TRANSLATE -->
      <div class="white-button xs wide">
        <div class="link normal ib">Tulkot ar MT</div>
      </div>
      <!-- MT TRANSLATE END -->
      <!-- TRANSLATE 100% -->
      <div class="white-button xs wide">
        <div class="link normal ib">Tulkot 100%</div>
      </div>
      <!-- TRANSLATE 100% END -->
      <div class="translator-toolbox-buttons">
        <!-- ORIGINAL DOWNLOAD -->
        <div class="white-button xs x-wide">
          <div class="white-button-icon">
            <svgicon
              class="svg-icon"
              name="download"
              height="24"
            />
          </div>
          <div class="link normal ib">Orģināls</div>
        </div>
        <!-- ORIGINAL DOWNLOAD END -->
        <!-- ORIGINAL DOWNLOAD -->
        <div class="white-button xs x-wide button-red">
          <div class="white-button-icon">
            <svgicon
              class="svg-icon icon-white"
              name="download"
              height="24"
            />
          </div>
          <div class="link normal ib">Tulkojums</div>
        </div>
        <!-- ORIGINAL DOWNLOAD END -->
      </div>
    </div>
    <div class="relative mt-16">
      <div class="relative ib">
        <svgicon
          class="svg-icon icon-blueish-darker-still placeholder"
          name="search"
          height="24"
        />
        <input
          class="input w-528 w-placeholder"
          type="text"
          placeholder="Atlasīt no sākotnējā teksta"
        >
      </div>
      <div class="absolute-right">
        <svgicon
          class="svg-icon icon-blueish-darker-still placeholder"
          name="search"
          height="24"
        />
        <input
          class="input w-528 w-placeholder"
          type="text"
          placeholder="Atlasīt no tulkotā teksta"
        >
      </div>
    </div>
  </div>
</template>
<script>
export default {
  name: 'TranslatorToolbox'
}
</script>
