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
          class="tabber-section last"
          @click="activeTab = 'comments'"
        >
          {{ $lang.titles.comments }}
        </div>
        <span
          v-if="commentCount > 0"
          class="comment-count"
        >{{ commentCount }}</span>
      </div>
      <transition
        name="ffade"
        mode="out-in">
        <div
          v-if="activeTab === 'translate'"
          class="tab"
        >
          <span
            v-if="!showConcordance"
          >
            <label
              class="input-label"
              for="mt"
            >{{ $lang.titles.mt }}</label>
            <div class="select-container">
              <v-select
                id="mt"
                v-model="activeSystem"
                :options="systems"
                name="mt"
                @input="onMtChange"
              />
            </div>
            <div
              v-if="$store.state.activeSegment"
              class="mt-24"
            >
              <label class="input-label">{{ $lang.titles.suggestions }}</label>
              <img
                v-if="!$store.state.activeSegment.mtMatchLoaded"
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
                    :id="'suggestion-' + index"
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
                        v-html="convertTags(suggestion.translation, $store.state.activeSegment.id)"
                      />
                      <div
                        v-else
                        :title="$lang.tooltips.created_by + ': ' + suggestion.createdBy"
                      >
                        <div
                          class="mb-8 bb-light-darker"
                          v-html="convertTags(suggestion.segment, $store.state.activeSegment.id)"
                        />
                        <div v-html="convertTags(suggestion.translation, $store.state.activeSegment.id)" />
                      </div>
                    </div>
                    <div
                      :class="{'high-match': suggestion.rawMatch > 69, 'mid-match': suggestion.rawMatch > 49 && suggestion.rawMatch < 70, 'mt-match': suggestion.isMT}"
                      class="suggestion-match"
                    >{{ suggestion.match }}</div>
                  </div>
                </transition-group>
              </div>
              <img
                v-if="!$store.state.activeSegment.suggestionsLoaded && $store.state.activeSegment.mtMatchLoaded"
                :src="$assetPath + 'loading.svg'"
                class="splash-image"
                height="48"
              >
            </div>
          </span>
          <div
            v-shortkey="['shift', 'enter']"
            class="relative mt-32 mb-8 mr-32"
            @shortkey="openTermSearch"
          >
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
          <div
            class="button terms"
            @click="openTermSearch"
          >{{ $lang.buttons.search_in_terms }}</div>
          <div
            class="button terms"
            @click="openSynonymSearch"
          >{{ $lang.buttons.search_in_synonym }}</div>
          <div
            class="button terms"
            @click="openConcordanceSearch"
          >{{ $lang.buttons.search_in_concordance }}</div>
          <div
            v-if="showConcordance"
            id="concordance-list"
            :style="{'max-height': concordanceListHeight + 'px'}"
            class="mt-8"
          >
            <div
              v-for="(match, index) in concordanceMatches"
              :key="index + 1"
              :class="{'last': index === concordanceMatches.length - 1}"
              class="suggestion"
            >
              <div class="suggestion-nr">{{ index + 1 }}</div>
              <div
                class="suggestion-text concordance"
              >
                <div>
                  <div
                    class="mb-8 bb-light-darker"
                    v-html="match.segment"
                  />
                  <div v-html="match.translation" />
                </div>
              </div>
            </div>
          </div>
        </div>
        <div
          v-if="activeTab === 'hotkeys'"
          class="tab"
        >
          <div class="input-label mt-24 size-s-i">
            {{ $lang.titles.hotkeys }}
          </div>
          <div class="hotkey-row bt-light-darker">
            <div class="ib">{{ $lang.hotkeys.previous_unconfirmed_segment }}</div>
            <div class="ib pull-right">
              <div class="hotkey-container">
                Ctrl
              </div>
              +
              <div class="hotkey-container">
                &uarr;
              </div>
            </div>
          </div>
          <div class="hotkey-row">
            <div class="ib">{{ $lang.hotkeys.next_unconfirmed_segment }}</div>
            <div class="ib pull-right">
              <div class="hotkey-container">
                Ctrl
              </div>
              +
              <div class="hotkey-container">
                &darr;
              </div>
            </div>
          </div>
          <div class="hotkey-row">
            <div class="ib">{{ $lang.hotkeys.confirm_segment }}</div>
            <div class="ib pull-right">
              <div class="hotkey-container">
                Ctrl
              </div>
              +
              <div class="hotkey-container">
                &#9166;
              </div>
            </div>
          </div>
          <div class="hotkey-row">
            <div class="ib">{{ $lang.hotkeys.delete_segment }}</div>
            <div class="ib pull-right">
              <div class="hotkey-container">
                Ctrl
              </div>
              +
              <div class="hotkey-container">
                D
              </div>
            </div>
          </div>
          <div class="hotkey-row">
            <div class="ib">{{ $lang.hotkeys.copy_source_to_target }}</div>
            <div class="ib pull-right">
              <div class="hotkey-container">
                Ctrl
              </div>
              +
              <div class="hotkey-container">
                Insert
              </div>
            </div>
          </div>
          <div class="hotkey-row">
            <div class="ib">{{ $lang.hotkeys.select_suggestion }}</div>
            <div class="ib pull-right">
              <div class="hotkey-container">
                Ctrl
              </div>
              +
              <div class="hotkey-container">
                1, 2, ...
              </div>
            </div>
          </div>
          <div class="hotkey-row">
            <div class="ib">{{ $lang.hotkeys.search_term }}</div>
            <div class="ib pull-right">
              <div class="hotkey-container">
                Shift
              </div>
              +
              <div class="hotkey-container">
                &#9166;
              </div>
            </div>
          </div>
          <div class="hotkey-row">
            <div class="ib">{{ $lang.hotkeys.select_autocomplete }}</div>
            <div class="ib pull-right">
              <div class="hotkey-container">
                Ctrl
              </div>
              +
              <div class="hotkey-container">
                A
              </div>
            </div>
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
            <svgicon
              v-if="isSearching && commentSearch !== ''"
              class="svg-icon icon-blueish-darker-still clear-single-search"
              name="close-circle"
              height="24"
              @click="() => { commentSearch = ''; $emit('clearCommentsSearch') }"
            />
          </div>
          <div class="input-label ib-i mb-0-i mt-24 va-top">{{ $lang.titles.comments }}</div>
          <div
            v-if="resolvable"
            class="link ib pull-right mt-24 va-top"
            @click="resolveComment"
          >{{ $lang.buttons.resolve_all }}</div>
          <div class="bb-blueish mt-4"/>
          <transition-group
            name="ffade"
            mode="out-in"
          >
            <span
              v-if="$store.state.activeSegment"
              :key="22"
            >
              <div
                v-for="(comment, index) in $store.state.activeSegment.comments"
                :key="index"
                class="size-s dark"
              >
                <div
                  v-if="parseInt(comment.message_type) === 1"
                  class="mt-8"
                >
                  <div class="size-xs bold blueish-darker-still ib">{{ comment.full_name }}</div>
                  <div class="size-xs blueish-darker-still ib">{{ timeToDateString(comment.timestamp) }}</div>
                  <div class="size-s dark">{{ comment.message }}</div>
                </div>
                <div
                  v-if="parseInt(comment.message_type) === 2"
                  class="bb-red-big mb-16"
                >
                  <div class="size-xs red bold">{{ comment.full_name }} {{ $lang.messages.resolved_comments }}</div>
                </div>
              </div>
            </span>
          </transition-group>
          <div class="input-label mt-24 va-top">{{ $lang.titles.add_comment }}</div>
          <textarea
            v-autosize
            v-model="newComment.message"
            :min-height="1"
            :placeholder="$lang.inputs.write_comment"
            rows="1"
            class="input w-100p"
          />
          <br>
          <button
            class="button"
            @click="saveComment"
          >{{ $lang.buttons.publish }}</button>
        </div>
      </transition>
    </div>
    <div class="number-col">
      &nbsp;
    </div>
  </div>
</template>
<script>
import CommentsService from 'services/comments'
import SegmentsService from 'services/segments'
import _ from 'lodash'
import {DateConverter} from 'utils/date-converter'
import {
  xliffToHtml
} from 'utils/segment/segment-text'
export default {
  name: 'TranslatorAssistant',
  props: {
    maxHeight: {
      type: Number,
      required: true
    },
    fromLang: {
      type: String,
      required: true
    },
    toLang: {
      type: String,
      required: true
    },
    searchedTerm: {
      type: String,
      default: ''
    },
    systems: {
      type: Array,
      required: true
    },
    system: {
      type: Object,
      required: true
    },
    isSearching: {
      type: Boolean,
      default: false
    }
  },
  data: function () {
    return {
      activeTab: 'translate',
      newComment: {},
      commentSearch: '',
      searchTerm: '',
      activeSystem: null,
      concordanceMatches: null,
      concordanceListHeight: 200
    }
  },
  computed: {
    resolvable: function () {
      if (this.$store.state.activeSegment === null) {
        return false
      }
      if (typeof (this.$store.state.activeSegment.comments) === 'undefined' || this.$store.state.activeSegment.comments.length < 1) {
        return false
      }
      return this.$store.state.activeSegment.comments[this.$store.state.activeSegment.comments.length - 1].thread_id === null
    },
    commentCount: function () {
      let count = 0
      if (this.$store.state.activeSegment === null) {
        return count
      }
      _.map(this.$store.state.activeSegment.comments, el => {
        if (parseInt(el.message_type) === 1) {
          count++
        }
      })
      return count
    },
    showConcordance: function () {
      return this.concordanceMatches && this.concordanceMatches.length > 0
    }
  },
  watch: {
    searchedTerm: function (newVal) {
      this.searchTerm = newVal
    },
    system: function (newVal) {
      this.activeSystem = newVal
    }
  },
  mounted: function () {
    this.resetComment()
    this.$nextTick(function () {
      window.addEventListener('resize', this.setConcordanceListHeight)
      this.setConcordanceListHeight()
    })
  },
  beforeDestroy: function () {
    window.removeEventListener('resize', this.setConcordanceListHeight)
  },
  methods: {
    setSuggestion: function (suggestion) {
      this.$store.state.activeSegment.translation = suggestion.translation
      this.$store.state.activeSegment.focusToggle = !this.$store.state.activeSegment.focusToggle
      if (suggestion.match === 'MT') {
        this.$store.state.activeSegment.saveType = 'MT'
      } else {
        this.$store.state.activeSegment.saveType = 'TM'
        this.$store.state.activeSegment.match = parseInt(suggestion.match)
      }
      this.$store.commit('recalculateUnusedTags', 'dud')
    },
    resetComment: function () {
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
          this.resetComment()
          this.$loading.endLoading('add-comment')
          if (typeof (r.data.data.entries[0]) === 'undefined') {
            this.$Alerts.add(this.$lang.messages.comment_save_error)
          } else {
            this.$store.state.activeSegment.comments.push(r.data.data.entries[0])
          }
        })
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
      window.open(this.$store.state.termBaseUrl + this.searchTerm + '/' + this.fromLang.substring(0, 2) + '?target=' + this.toLang.substring(0, 2), '_blank').focus()
    },
    openSynonymSearch: function () {
      if (this.searchTerm === '') return
      let url = this.$store.state.synonymBaseUrl + this.$lang.getLang()
      url += '?systemid=' + this.activeSystem.value
      url += '&text=' + this.searchTerm
      window.open(url, '_blank').focus()
    },
    openConcordanceSearch: function () {
      if (this.searchTerm === '') return
      SegmentsService.getConcordanceContribution({
        text: this.searchTerm,
        source: this.fromLang,
        target: this.toLang
      })
        .then(response => {
          this.concordanceMatches = null
          this.concordanceMatches = []
          if (typeof (response.data.data.matches) !== 'undefined') {
            const matches = response.data.data.matches
            for (let i = 0; i < matches.length; i++) {
              this.concordanceMatches.push({
                segment: matches[i].raw_segment,
                translation: matches[i].translation
              })
            }
          }
        })
    },
    convertTags: function (text, parentId) {
      return xliffToHtml(text, parentId)
    },
    onMtChange: function (value) {
      this.$emit('mtSystemChange', value)
    },
    setConcordanceListHeight: function () {
      const appHeight = document.getElementById('cat-app').clientHeight
      this.concordanceListHeight = appHeight - 203
    }
  }
}
</script>
