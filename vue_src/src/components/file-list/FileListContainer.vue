<template>
  <div>
    <div class="file-list-container">
      <!-- FILE LIST HEADER -->
      <div class="file-list-header">
        <div class="status">Statuss</div>
        <div class="segments">Segmenti</div>
        <div class="words">Vārdi</div>
        <div class="translated">Iztulkots</div>
        <div class="created">Ielādes datums</div>
        <div class="controls">&nbsp;</div>
      </div>
      <!-- FILE LIST HEADER END -->
      <!-- FILE LIST -->
      <div class="file-list">
        <transition-group
          name="fade"
          mode="out-in">
          <div
            v-for="(file, key) in files"
            :key="key"
            class="file-row"
          >
            <div class="status column">
              <svgicon
                class="svg-icon va-middle"
                name="file"
                height="24"
              />
              {{ file.name }}</div>
            <div
              class="additional-row"
            >
              <span v-if="$loading.isLoading('file_' + key)">
                {{ file.loadingStatus === 'UPLOADING' ? 'Augšupielādējas' : 'Analizējas' }}
                <img
                  :src="$assetPath + 'ajax-loader.gif'"
                  class="ib ml-16"
                >
              </span>
              <span v-else>
                <div class="segments column">{{ file.segmentCount }}</div>
                <div class="words column">{{ file.wordCount }}</div>
                <div class="translated column">{{ file.progress }} %</div>
                <div class="created column">{{ file.created }}</div>
                <div class="controls column">
                  <!-- TRANSLATE -->
                  <div
                    class="icon-span mr-24"
                    @click="translate(key)"
                  >
                    <svgicon
                      class="svg-icon va-middle"
                      name="translation-assist"
                      height="24"
                    />
                    <div class="link ib">Tulkot</div>
                  </div>
                  <!-- TRANSLATE END -->
                  <!-- DOWNLOAD -->
                  <div
                    class="icon-span mr-24"
                    @click="downloadFile(file.translatedUrl)"
                  >
                    <svgicon
                      class="svg-icon va-middle"
                      name="download"
                      height="24"
                    />
                    <div class="link ib">Lejupielādēt</div>
                  </div>
                  <!-- DOWNLOAD END -->
                  <!-- DELETE -->
                  <div
                    class="icon-span"
                    @click="removeFile(key)"
                  >
                    <svgicon
                      class="svg-icon va-middle"
                      name="close"
                      height="24"
                    />
                    <div class="link ib">Dzēst</div>
                  </div>
                  <!-- DELETE END -->
                </div>
              </span>
            </div>
          </div>
        </transition-group>
      </div>
      <!-- FILE LIST END -->
    </div>
    <transition
      name="fade"
      mode="out-in"
    >
      <confirmation
        v-if="showFileDeleteConfirm"
        confirm-text="Dzēst"
        cancel-text="Atcelt"
        @confirm="deleteFile"
        @cancel="cancelFileDelete"
      >
        Vai tiešām vēlaties dzēst failu?
      </confirmation>
    </transition>
  </div>
</template>

<script>
import FileService from 'services/file'
import {Confirmation} from '@shibetec/vue-toolbox'
export default {
  name: 'FileListContainer',
  components: {
    'confirmation': Confirmation
  },
  props: {
    fileList: {
      type: Array,
      required: true
    }
  },
  data: function () {
    return {
      files: [],
      showFileDeleteConfirm: false,
      activeFileDeleteKey: null
    }
  },
  watch: {
    fileList: function (newVal, oldVal) {
      this.files = newVal
    }
  },
  methods: {
    removeFile: function (key) {
      this.activeFileDeleteKey = key
      this.showFileDeleteConfirm = false
      this.showFileDeleteConfirm = true
    },
    share: function (key) {
      // TODO Implement file sharing
    },
    deleteFile: function () {
      if (this.activeFileDeleteKey === null) return
      const data = {
        new_status: 'cancelled',
        res: 'job',
        id: this.files[this.activeFileDeleteKey].jobId,
        password: this.files[this.activeFileDeleteKey].jobPassword
      }
      FileService.delete(data)
        .then(r => {
          this.files.splice(this.activeFileDeleteKey, 1)
          this.activeFileDeleteKey = null
          this.showFileDeleteConfirm = false
        })
    },
    cancelFileDelete: function () {
      this.activeFileDeleteKey = null
      this.showFileDeleteConfirm = false
    },
    translate: function (key) {
      this.$router.push({name: 'translate', params: {jobId: this.files[key].jobId, password: this.files[key].jobPassword}})
    },
    downloadFile: function (link) {
      window.location.href = link
    }
  }
}
</script>
