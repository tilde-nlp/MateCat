import {HTTP} from './base'
import {FormGenerator} from './form-generator'
import {CONFIG} from '../CONFIG'
export default {
  getSegments: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=getSegments', FormGenerator.generateForm(data))
  },
  setTranslation: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=setTranslation', FormGenerator.generateForm(data))
  },
  getContribution: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=getContribution', FormGenerator.generateForm(data))
  },
  getSpellcheck: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=getSpellcheck', FormGenerator.generateForm(data))
  },
  setCurrent: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=setCurrentSegment&time=' + new Date().getTime() + ',jid=' + data.id_job + ',sid=' + data.id_segment, FormGenerator.generateForm(data))
  },
  setSegmentSplit: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=setSegmentSplit&&time=' + new Date().getTime() + ',jid=' + data.id_job + ',sid=' + data.id_segment, FormGenerator.generateForm(data))
  },
  searchSegments: function (data) {
    return HTTP.post(CONFIG.baseUrl + '?action=getSearch&time=' + new Date().getTime() + ',jid=' + data.id_job, FormGenerator.generateForm(data))
  }
}
