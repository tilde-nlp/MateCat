<template>
  <div>
    <div class="mt-16">
      <!-- UNDO -->
      <div class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon"
            name="arrow"
            height="24"
          />
        </div>
      </div>
      <!-- UNDO END -->
      <!-- REDO -->
      <div class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon flip-h"
            name="arrow"
            height="24"
          />
        </div>
      </div>
      <!-- REDO END -->
      <!-- CONFIRM -->
      <div class="white-button xs mr-32-i">
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
      <div class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon"
            name="arrow"
            height="24"
          />
        </div>
      </div>
      <!-- SPLIT END -->
      <!-- JOIN -->
      <div class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon"
            name="arrow"
            height="24"
          />
        </div>
      </div>
      <!-- JOIN END -->
      <!-- CANCEL -->
      <div class="white-button xs">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon"
            name="close"
            height="24"
          />
        </div>
      </div>
      <!-- CANCEL END -->
      <!-- FORWARD -->
      <div class="white-button xs mr-32-i">
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
      <div class="white-button xs">
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
      <div class="white-button xs">
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
            name="text"
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
      <div class="absolute-right">
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
