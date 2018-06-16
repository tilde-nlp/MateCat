<template>
  <div>
    <div class="pager-container">
      <div class="white-button big left">
        <div class="white-button-icon">
          <svgicon
            class="svg-icon va-middle"
            name="arrow"
            height="24"
          />
        </div>
        <div class="link normal ib left">Atpakaļ</div>
      </div>
      <div class="pages">
        <div class="white-button small">
          <div class="link center normal">1</div>
        </div>
        <div class="white-button small">
          <div class="link center normal">2</div>
        </div>
        <div class="white-button small active">
          <div class="link center normal">3</div>
        </div>
        <div class="white-button small">
          <div class="link center normal">4</div>
        </div>
        <div class="white-button small">
          <div class="link center normal">5</div>
        </div>
      </div>
      <div class="white-button big right">
        <div class="link normal ib right">Tālāk</div>
        <div class="white-button-icon">
          <svgicon
            class="svg-icon va-middle r-180"
            name="arrow"
            height="24"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FileListPager'
}
</script>
