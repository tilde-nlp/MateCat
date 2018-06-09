<template>
  <div class="translator-section toolbox">
    <div class="pt-8 mb-16 ml-32 mr-16">
      <div
        class="icon-container">
        <svgicon
          class="svg-icon"
          name="undo"
          height="30"
        />
      </div>
      <div
        class="icon-container mr-24">
        <svgicon
          class="svg-icon flip-h"
          name="undo"
          height="30"
        />
      </div>
      <div
        class="icon-container">
        <svgicon
          class="svg-icon"
          name="check"
          height="30"
        />
      </div>
      <div
        class="icon-container w-48 center">
        Split
      </div>
      <div
        class="icon-container w-48 center mr-24">
        Join
      </div>
      <div
        class="icon-container">
        <svgicon
          class="svg-icon"
          name="close-circle"
          height="30"
        />
      </div>
      <div
        class="icon-container">
        <svgicon
          class="svg-icon"
          name="arrow-right-bold"
          height="30"
        />
      </div>
      <div
        class="icon-container">
        <svgicon
          class="svg-icon flip-h"
          name="chevron"
          height="30"
        />
      </div>
      <div
        class="icon-container  mr-24">
        <svgicon
          class="svg-icon"
          name="chevron"
          height="30"
        />
      </div>
      <div
        class="icon-container mr-24">
        <svgicon
          class="svg-icon"
          name="format-pilcrow"
          height="30"
        />
      </div>
      <div
        class="icon-container mr-24">
        <svgicon
          class="svg-icon"
          name="format-color"
          height="30"
        />
      </div>
      <div
        class="icon-container pl-4 pr-4">
        Izmantot tulkojumu ar 100% atbilstību
      </div>
      <div
        class="icon-container pl-4 pr-4">
        Izmantot MT
      </div>
      <div class="ib pull-right">
        <div
          class="icon-container">
          <svgicon
            class="svg-icon"
            name="file-outline"
            height="30"
          />
        </div>
        <div
          class="icon-container">
          <svgicon
            class="svg-icon"
            name="file-outline"
            height="30"
          />
        </div>
      </div>
    </div>
    <div class="ml-32 pb-8 mr-16">
      <input
        type="text"
        placeholder="Atlasīt no sākotnējā teksta"
      >
      <input
        type="text"
        placeholder="Atlasīt no tulkotā teksta"
      >
      <input
        type="text"
        placeholder="Izvēlēties atlases kritērijus"
      >
    </div>
  </div>
</template>
<script>
export default {
  name: 'TranslatorToolbox'
}
</script>
