<template>
  <div class="triple-block h-100p">
    <div class="segment-col assistant">
      <div class="tabber">
        <div
          :class="{active: activeTab === 'translate'}"
          class="tabber-section"
          @click="activeTab = 'translate'"
        >
          {{ $lang.titles.translations }}
        </div>
        <div
          :class="{active: activeTab === 'hotkeys'}"
          class="tabber-section"
          @click="activeTab = 'hotkeys'"
        >
          {{ $lang.titles.shortkeys }}
        </div>
        <div
          :class="{active: activeTab === 'comments'}"
          class="tabber-section"
          @click="activeTab = 'comments'"
        >
          {{ $lang.titles.comments }}
        </div>
      </div>
      <transition
        name="ffade"
        mode="out-in">
        <div
          v-if="activeTab === 'translate'"
          class="tab"
        >
          <label
            class="input-label"
            for="mt"
          >{{ $lang.titles.mt }}</label>
          <div class="select-container">
            <v-select
              id="mt"
              v-model="system"
              :options="systems"
              name="mt"
              @input="value => {$emit('mtSystemChange', value)}"
            />
          </div>
          <div
            v-if="$store.state.activeSegment"
            class="mt-24"
          >
            <label class="input-label">{{ $lang.titles.suggestions }}</label>
            <img
              v-if="!$store.state.activeSegment.suggestionsLoaded"
              :src="$assetPath + 'loading.svg'"
              class="splash-image"
              height="48"
            >
            <div
              v-else-if="$store.state.activeSegment.suggestions.length < 1">
              {{ $lang.titles.no_suggestions }}
            </div>
            <div
              v-else
              :style="{'max-height': maxHeight + 'px'}"
              class="suggestions-container"
            >
              <transition-group
                name="ffade"
                mode="out-in"
              >
                <div
                  v-shortkey="['ctrl', parseInt(index + 1)]"
                  v-for="(suggestion, index) in $store.state.activeSegment.suggestions"
                  :key="index"
                  :class="{'last': index === $store.state.activeSegment.suggestions.length - 1}"
                  class="suggestion"
                  @click="setSuggestion(suggestion)"
                  @shortkey="setSuggestion(suggestion)"
                >
                  <div class="suggestion-nr">{{ index + 1 }}</div>
                  <div
                    :class="{'high-match': suggestion.rawMatch > 69, 'mid-match': suggestion.rawMatch > 49 && suggestion.rawMatch < 70, 'mt-match': suggestion.isMT}"
                    class="suggestion-text"
                  >
                    <span
                      v-if="suggestion.isMT"
                      v-html="convertTags(suggestion.translation)"
                    />
                    <div
                      v-else
                      :title="$lang.tooltips.created_by + ': ' + suggestion.createdBy + '; ' + $lang.tooltips.usage_count + ': ' + suggestion.usageCount"
                    >
                      <div
                        class="mb-8 bb-light-darker"
                        v-html="convertTags(suggestion.segment)"
                      />
                      <div v-html="convertTags(suggestion.translation)" />
                    </div>
                  </div>
                  <div
                    :class="{'high-match': suggestion.rawMatch > 69, 'mid-match': suggestion.rawMatch > 49 && suggestion.rawMatch < 70, 'mt-match': suggestion.isMT}"
                    class="suggestion-match"
                  >{{ suggestion.match }}</div>
                </div>
              </transition-group>
            </div>
          </div>
          <div class="relative mt-32 mb-8 mr-32">
            <svgicon
              class="svg-icon icon-blueish-darker-still placeholder"
              name="search"
              height="24"
            />
            <input
              v-model="searchTerm"
              :placeholder="$lang.inputs.search_term"
              class="search-input"
              type="text"
              name="terms-search"
              @keyup.enter="openTermSearch"
            >
          </div>
        </div>
        <div
          v-if="activeTab === 'comments'"
          class="tab"
        >
          <div class="relative mt-8 mb-8 mr-32">
            <svgicon
              class="svg-icon icon-blueish-darker-still placeholder"
              name="search"
              height="24"
            />
            <input
              v-model="commentSearch"
              :placeholder="$lang.inputs.search_in_comments"
              class="search-input"
              type="text"
              name="comments-search"
              @keyup.enter="() => { $emit('search') }"
              @input="e => { $emit('commentSearchInput', e.target.value) }"
            >
          </div>
          <transition-group
            name="ffade"
            mode="out-in"
          >
            <div
              v-for="(comment, index) in $store.state.activeSegment.comments"
              :key="index"
              class="size-s dark"
            >
              <div
                v-if="parseInt(comment.message_type) === 1"
                class="bb-blueish mb-8"
              >
                <div class="size-xs bold dark">{{ comment.full_name }}</div>
                <div class="size-xs dark">{{ comment.message }}</div>
                <div class="size-xs grey">{{ timeToDateString(comment.timestamp) }}</div>
              </div>
              <div
                v-if="parseInt(comment.message_type) === 2"
                class="bb-red-big mb-16"
              >
                <div class="size-xs red bold">{{ comment.full_name }} {{ $lang.messages.resolved_comments }}</div>
              </div>
            </div>
          </transition-group>
          <textarea
            v-autosize
            v-if="newComment !== null"
            v-model="newComment.message"
            :min-height="1"
            :placeholder="$lang.inputs.write_comment"
            rows="1"
            class="input w-100p"
          />
          <br>
          <button
            v-if="resolvable"
            class="button"
            @click="resolveComment"
          >{{ $lang.buttons.resolve_comments }}</button>
          <button
            v-if="newComment === null"
            class="button"
            @click="addComment"
          >{{ $lang.buttons.add_comment }}</button>
          <button
            v-if="newComment !== null"
            class="button"
            @click="saveComment"
          >{{ $lang.buttons.save }}</button>
          <button
            v-if="newComment !== null"
            class="button"
            @click="cancelComment"
          >{{ $lang.buttons.cancel }}</button>
        </div>
      </transition>
    </div>
    <div class="number-col">
      &nbsp;
    </div>
  </div>
</template>
<script>
import LanguagesService from 'services/languages.js'
import CommentsService from 'services/comments.js'
import _ from 'lodash'
import {DateConverter} from 'utils/date-converter'
import {TagsConverter} from 'utils/tags-converter'
export default {
  name: 'TranslatorAssistant',
  props: {
    maxHeight: {
      type: Number,
      required: true
    }
  },
  data: function () {
    return {
      activeTab: 'translate',
      systems: [],
      system: null,
      newComment: null,
      commentSearch: '',
      searchTerm: ''
    }
  },
  computed: {
    resolvable: function () {
      if (typeof (this.$store.state.activeSegment.comments) === 'undefined' || this.$store.state.activeSegment.comments.length < 1) {
        return false
      }
      return this.$store.state.activeSegment.comments[this.$store.state.activeSegment.comments.length - 1].thread_id === null
    }
  },
  mounted: function () {
    LanguagesService.getMTSystems()
      .then(langsRes => {
        this.systems = _.map(langsRes.data, el => {
          return {
            label: el.Title.Text,
            value: el.ID
          }
        })
        this.system = this.systems[0]
        this.$emit('mtSystemChange', this.system)
      })
  },
  methods: {
    setSuggestion: function (suggestion) {
      this.$store.state.activeSegment.translation = suggestion.translation
      if (suggestion.match === 'MT') {
        this.$store.state.activeSegment.saveType = 'MT'
      } else {
        this.$store.state.activeSegment.saveType = 'TM'
        this.$store.state.activeSegment.match = parseInt(suggestion.match)
      }
    },
    addComment: function () {
      this.newComment = {
        action: 'comment',
        _sub: 'create',
        id_client: '???',
        id_job: 0,
        id_segment: 0,
        username: this.$store.state.profile.first_name + ' ' + this.$store.state.profile.last_name,
        password: '',
        source_page: 1,
        message: '',
        date: ''
      }
    },
    saveComment: function () {
      if (this.newComment === null) return
      if (this.newComment.message === '') {
        this.$Alerts.add(this.$lang.messages.empty_comment)
        return
      }
      this.$loading.startLoading('add-comment')
      this.newComment.id_job = this.$store.state.activeSegment.jobId
      this.newComment.id_segment = this.$store.state.activeSegment.id
      this.newComment.password = this.$store.state.activeSegment.jobPassword
      CommentsService.doAction(this.newComment)
        .then(r => {
          this.newComment = null
          this.$loading.endLoading('add-comment')
          if (typeof (r.data.data.entries[0]) === 'undefined') {
            this.$Alerts.add(this.$lang.messages.comment_save_error)
          } else {
            this.$store.state.activeSegment.comments.push(r.data.data.entries[0])
          }
        })
    },
    cancelComment: function () {
      this.newComment = null
    },
    resolveComment: function () {
      const data = {
        action: 'comment',
        _sub: 'resolve',
        id_job: this.$store.state.activeSegment.jobId,
        id_client: '???',
        id_segment: this.$store.state.activeSegment.id,
        password: this.$store.state.activeSegment.jobPassword,
        source_page: 1,
        username: this.$store.state.profile.first_name + ' ' + this.$store.state.profile.last_name
      }
      CommentsService.doAction(data)
        .then(r => {
          if (typeof (r.data.data.entries[0]) === 'undefined') {
            this.$Alerts.add(this.$lang.messages.comment_save_error)
          } else {
            this.$store.state.activeSegment.comments.push(r.data.data.entries[0])
          }
        })
    },
    timeToDateString: function (timestamp) {
      return DateConverter.timeStampToFullTime(timestamp)
    },
    openTermSearch: function () {
      if (this.searchTerm === '') return
      window.open('http://termini.lza.lv/term.php?term=' + this.searchTerm + '&lang=LV', '_blank')
    },
    convertTags: function (text) {
      return TagsConverter.add(text)
    }
  }
}
</script>
