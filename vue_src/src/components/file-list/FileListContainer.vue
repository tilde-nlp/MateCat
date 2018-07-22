<template>
  <div class="file-list-container">
    <!-- FILE LIST HEADER -->
    <div class="file-list-header">
      <div class="status">{{ $lang.titles.status }}</div>
      <div class="direction">{{ $lang.titles.direction }}</div>
      <div class="segments">{{ $lang.titles.segments }}</div>
      <div class="words">{{ $lang.titles.words }}</div>
      <div class="translated">{{ $lang.titles.translated }}</div>
      <div class="created">{{ $lang.titles.date_added }}</div>
      <div class="controls">&nbsp;</div>
    </div>
    <!-- FILE LIST HEADER END -->
    <!-- FILE LIST -->
    <div class="file-list">
      <transition-group
        name="ffade"
        mode="out-in"
      >
        <div
          v-for="(file, key) in files"
          :key="key"
          class="file-row"
          @click="translate(key)"
        >
          <div class="status column">
            <svgicon
              :class="{'icon-green': file.progress >= 100}"
              class="svg-icon va-middle static"
              name="file"
              height="24"
            />
            {{ file.name }}</div>
          <div
            class="additional-row"
          >
            <span
              v-if="file.isEmpty"
              class="red"
            >
              {{ $lang.messages.broken_file_upload }}
              <!-- DELETE -->
              <div
                v-if="file.jobId > 0"
                class="icon-span ml-77"
                @click.prevent.stop="fastDeleteFile(key)"
              >
                <svgicon
                  class="svg-icon va-middle icon-blueish-darker-still"
                  name="trash"
                  height="24"
                />
                <div class="link ib">{{ $lang.buttons.delete }}</div>
              </div>
              <!-- DELETE END -->
            </span>
            <span v-else>
              <div class="direction column">
                {{ file.direction }}
              </div>
              <div class="segments column">
                <svgicon
                  v-if="file.segmentCount < 0"
                  class="svg-loading va-middle"
                  name="loading"
                  height="24"
                />
                <span v-else>{{ file.segmentCount }}</span>
              </div>
              <div class="words column">
                <svgicon
                  v-if="file.wordCount < 0"
                  class="svg-loading va-middle"
                  name="loading"
                  height="24"
                />
                <span v-else>{{ file.wordCount }}</span>
              </div>
              <div class="translated column">
                <svgicon
                  v-if="file.progress < 0"
                  class="svg-loading va-middle"
                  name="loading"
                  height="24"
                />
                <span v-else>{{ file.progress }} %</span>
              </div>
              <div class="created column">{{ file.created }}</div>
              <div class="controls column">
                <div
                  v-if="file.progress < 0"
                  class="icon-span mr-16 w-109"
                >
                  <svgicon
                    class="svg-loading va-middle"
                    name="loading"
                    height="24"
                  />
                </div>
                <!-- TRANSLATE -->
                <div
                  v-if="file.progress >= 0"
                  class="icon-span mr-24"
                  @click.prevent.stop="translate(key)"
                >
                  <svgicon
                    class="svg-icon va-middle"
                    name="translation-assist"
                    height="24"
                  />
                  <div class="link ib">{{ $lang.buttons.translate }}</div>
                </div>
                <!-- TRANSLATE END -->
                <!-- DOWNLOAD -->
                <div
                  v-if="file.progress >= 0"
                  class="icon-span mr-16"
                  @click.prevent.stop="downloadFile(file.translatedUrl)"
                >
                  <svgicon
                    class="svg-icon va-middle icon-blueish-darker-still"
                    name="download"
                    height="24"
                  />
                </div>
                <!-- DOWNLOAD END -->
                <!-- DELETE -->
                <div
                  v-if="file.progress >= 0"
                  class="icon-span"
                  @click.prevent.stop="removeFile(key)"
                >
                  <svgicon
                    class="svg-icon va-middle icon-blueish-darker-still"
                    name="trash"
                    height="24"
                  />
                </div>
                <!-- DELETE END -->
              </div>
            </span>
          </div>
        </div>
      </transition-group>
    </div>
    <!-- FILE LIST END -->
    <transition
      name="ffade"
      mode="out-in"
    >
      <confirmation
        v-if="showFileDeleteConfirm"
        :confirm-text="$lang.buttons.delete"
        :cancel-text="$lang.buttons.cancel"
        @confirm="deleteFile"
        @cancel="cancelFileDelete"
      >
        {{ $lang.messages.file_delete_confirm }}
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
    fastDeleteFile: function (key) {
      this.activeFileDeleteKey = key
      this.deleteFile()
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
          this.$emit('deleted')
        })
    },
    cancelFileDelete: function () {
      this.activeFileDeleteKey = null
      this.showFileDeleteConfirm = false
    },
    translate: function (key) {
      this.$router.push({name: 'translate', params: {projectId: this.files[key].id, ppassword: this.files[key].password, jobId: this.files[key].jobId, password: this.files[key].jobPassword}})
    },
    downloadFile: function (link) {
      window.location.href = link
    }
  }
}
</script>
