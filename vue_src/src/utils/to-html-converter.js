// TODO How to handle xlif ids of _48 and sorts?
// TODO Make global mousenter/leave listener
import {InvalidXlifTags} from './exceptions/invalid-xlif-tags'
import {ValueMissing} from './exceptions/value-missing'
import {ValueEmpty} from './exceptions/value-empty'
const selfClosingPattern = /&lt;.?.?.?.?.? id="[0-9]+"\/&gt;/g
const dualOpenPattern = /&lt;.?.?.?.?.? id="[0-9]+"&gt;/
const dualClosePattern = /&lt;\/.?.?.?.?.?&gt;/
export function replaceAllSelfClosedTags (inputText, segmentId) {
  if (typeof (inputText) === 'undefined' || inputText === null) {
    throw new ValueMissing()
  }
  const matches = inputText.match(selfClosingPattern)
  if (matches === null) {
    return inputText
  }
  let outputText = inputText
  matches.forEach(foundTag => {
    outputText = replaceOneSeflClosedTag(foundTag, outputText, segmentId)
  })
  return outputText
}
function replaceOneSeflClosedTag (tag, text, segmentId) {
  const id = getId(tag)
  const tagName = getTagName(tag)
  const htmlTagName = tagName
  const htmlTag = buildSelfClosingHtmlTag(htmlTagName, id, segmentId)
  return text.replace(tag, htmlTag)
}
function replaceAllDualsTags (text, segmentId) {
  let openTagPosition = text.search(dualOpenPattern)
  let closeTagPosition = text.search(dualClosePattern)
  if (openTagPosition < 0 && closeTagPosition >= 0) {
    throw InvalidXlifTags
  }
  if (openTagPosition < 0) {
    return text
  }
  let stack = []
  while (1) {
    let openTagPosition = text.search(dualOpenPattern)
    let closeTagPosition = text.search(dualClosePattern)
    if (openTagPosition < 0 && closeTagPosition < 0) {
      break
    }
    if (openTagPosition > -1 && openTagPosition < closeTagPosition) {
      const openTag = text.match(dualOpenPattern)[0]
      text = replaceOpenTag(text, openTag, segmentId)
      stack.push(toObject(openTag))
    } else {
      const closeTag = text.match(dualClosePattern)[0]
      const openTagObject = stack.pop()
      text = replaceCloseTag(text, closeTag, openTagObject, segmentId)
    }
  }
  if (stack.length > 0) {
    throw InvalidXlifTags
  }
  return text
}
function replaceOpenTag (text, tag, segmentId) {
  const tagId = getId(tag)
  const tagName = getTagName(tag)
  const htmlTagName = tagName
  const htmlTag = buildDualOpenHtmlTag(htmlTagName, tagId, segmentId)
  return text.replace(tag, htmlTag)
}
function replaceCloseTag (text, closeTag, openTagObject, segmentId) {
  const htmlTagName = openTagObject.name
  const htmlTag = buildDualCloseHtmlTag(htmlTagName, openTagObject.id, segmentId)
  return text.replace(closeTag, htmlTag)
}
function toObject (tag) {
  return {
    id: getId(tag),
    name: getTagName(tag)
  }
}
export function getId (tag) {
  validateString(tag)
  const idStartPosition = tag.indexOf('"')
  const idEndPosition = tag.indexOf('"', idStartPosition + 1)
  return tag.substring(idStartPosition + 1, idEndPosition)
}
export function getTagName (tag) {
  validateString(tag)
  const spacePosition = tag.indexOf(' ')
  return tag.substring(4, spacePosition)
}
export function validateString (text) {
  if (typeof (text) === 'undefined' || text === null) {
    throw new ValueMissing()
  }
  if (text === '') {
    throw new ValueEmpty()
  }
}
// TODO Only one class changes, these methods can be de-duplicated
export function buildSelfClosingHtmlTag (tagName, id, segmentId) {
  const template = '<span class="tag self-closing" data-tag-name="xxtagNamexx-sc" data-xlif-id="xxtagIdxx" data-class-id="tag-xxtagIdxx-xxsegmentIdxx" onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)">xxtagNamexx</span>'
  let result = template.replace(new RegExp('xxtagNamexx', 'g'), tagName)
  result = result.replace(new RegExp('xxtagIdxx', 'g'), id)
  result = result.replace(new RegExp('xxsegmentIdxx', 'g'), segmentId)
  return result
}
export function buildDualOpenHtmlTag (tagName, id, segmentId) {
  const template = '<span class="tag dual-open" data-tag-name="xxtagNamexx-do" data-xlif-id="xxtagIdxx" data-class-id="tag-xxtagIdxx-xxsegmentIdxx" onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)">xxtagNamexx</span>'
  let result = template.replace(new RegExp('xxtagNamexx', 'g'), tagName)
  result = result.replace(new RegExp('xxtagIdxx', 'g'), id)
  result = result.replace(new RegExp('xxsegmentIdxx', 'g'), segmentId)
  return result
}
export function buildDualCloseHtmlTag (tagName, id, segmentId) {
  const template = '<span class="tag dual-close" data-tag-name="xxtagNamexx-dc" data-xlif-id="xxtagIdxx" data-class-id="tag-xxtagIdxx-xxsegmentIdxx" onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)">xxtagNamexx</span>'
  let result = template.replace(new RegExp('xxtagNamexx', 'g'), tagName)
  result = result.replace(new RegExp('xxtagIdxx', 'g'), id)
  result = result.replace(new RegExp('xxsegmentIdxx', 'g'), segmentId)
  return result
}
export const ToHtmlConverter = {
  convert: function (text, segmentId) {
    const selfClosedReplaced = replaceAllSelfClosedTags(text, segmentId)
    return replaceAllDualsTags(selfClosedReplaced, segmentId)
  }
}
