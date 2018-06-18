<template>
  <div>
    <div class="pager-container">
      <transition
        name="fade"
      >
        <div
          v-if="currentPage > 1"
          class="white-button big left"
          @click="previousPage"
        >
          <div class="white-button-icon">
            <svgicon
              class="svg-icon va-middle"
              name="arrow"
              height="24"
            />
          </div>
          <div class="link normal ib left">Atpakaļ</div>
        </div>
      </transition>
      <div class="pages">
        <div
          v-for="n in pageControls"
          :class="{active: currentPage === n}"
          :key="n"
          class="white-button small"
          @click="goToPage(n)"
        >
          <div class="link center normal">{{ n }}</div>
        </div>
      </div>
      <div
        v-if="currentPage < pages"
        class="white-button big right"
        @click="nextPage"
      >
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
  name: 'FileListPager',
  props: {
    pages: {
      type: Number,
      required: true
    }
  },
  data: function () {
    return {
      currentPage: 1
    }
  },
  computed: {
    pageControls: function () {
      return Math.min(this.pages, 10)
    }
  },
  methods: {
    nextPage: function () {
      this.currentPage = Math.min(++this.currentPage, this.pages)
      this.$emit('pageChanged', this.currentPage)
    },
    previousPage: function () {
      this.currentPage = Math.max(--this.currentPage, 1)
      this.$emit('pageChanged', this.currentPage)
    },
    goToPage: function (page) {
      if (page === this.currentPage) return
      this.currentPage = page
      this.$emit('pageChanged', this.currentPage)
    }
  }
}
</script>
