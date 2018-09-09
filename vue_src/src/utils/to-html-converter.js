// TODO How to handle xlif ids of _48 and sorts?
import {RegisteredTags} from './registered-tags'
import {InvalidXlifTags} from './invalid-xlif-tags'
const selfClosingPattern = /&lt;.?.?.?.?.? id="[0-9]+"\/&gt;/g
const dualOpenPattern = /&lt;.?.?.?.?.? id="[0-9]+"&gt;/
const dualClosePattern = /&lt;\/.?.?.?.?.?&gt;/
function replaceSelfClosed (text, segmentId) {
  const matches = text.match(selfClosingPattern)
  if (matches === null) {
    return text
  }
  let result = text
  matches.forEach(tag => {
    result = replaceSeflClosedTag(tag, result, segmentId)
  })
  return result
}
function replaceSeflClosedTag (tag, text, segmentId) {
  const id = getId(tag)
  const tagName = getTagName(tag)
  const htmlTagName = tagName + '-scspan'
  registerHtmlTag(htmlTagName)
  const htmlTag = buildSelfClosingHtmlTag(htmlTagName, id, segmentId)
  return text.replace(tag, htmlTag)
}
function replaceDuals (text, segmentId) {
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
  const htmlTagName = tagName + '-dospan'
  registerHtmlTag(htmlTagName)
  const htmlTag = buildDualOpenHtmlTag(htmlTagName, tagId, segmentId)
  return text.replace(tag, htmlTag)
}
function replaceCloseTag (text, closeTag, openTagObject, segmentId) {
  const htmlTagName = openTagObject.name + '-dcspan'
  registerHtmlTag(htmlTagName)
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
  const idStartPosition = tag.indexOf('"')
  const idEndPosition = tag.indexOf('"', idStartPosition + 1)
  return parseInt(tag.substring(idStartPosition + 1, idEndPosition))
}
function getTagName (tag) {
  const spacePosition = tag.indexOf(' ')
  return tag.substring(4, spacePosition)
}
function registerHtmlTag (tagName) {
  if (RegisteredTags.indexOf(tagName) > -1) {
    return tagName
  }
  document.registerElement(tagName)
  RegisteredTags.push(tagName)
}
// TODO Only one class changes, these methods can be de-duplicated
function buildSelfClosingHtmlTag (tagName, id, segmentId) {
  const classId = 'data-class-id="tag-' + id + '-' + segmentId + '"'
  const classList = ' class="tag self-closing"'
  const xlifId = ' data-xlif-id="' + id + '"'
  const events = 'onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)"'
  return '<' + tagName + classList + xlifId + classId + events + '>' + id + '</' + tagName + '>'
}
function buildDualOpenHtmlTag (tagName, id, segmentId) {
  const classId = 'data-class-id="tag-' + id + '-' + segmentId + '"'
  const classList = ' class="tag dual-open"'
  const xlifId = ' data-xlif-id="' + id + '"'
  const events = 'onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)"'
  return '<' + tagName + classList + xlifId + classId + events + '>' + id + '</' + tagName + '>'
}
function buildDualCloseHtmlTag (tagName, id, segmentId) {
  const classId = 'data-class-id="tag-' + id + '-' + segmentId + '"'
  const classList = ' class="tag dual-close"'
  const xlifId = ' data-xlif-id="' + id + '"'
  const events = 'onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)"'
  return '<' + tagName + classList + xlifId + classId + events + '>' + id + '</' + tagName + '>'
}
export const ToHtmlConverter = {
  convert: function (text, segmentId) {
    const selfClosedReplaced = replaceSelfClosed(text, segmentId)
    const dualsReplaced = replaceDuals(selfClosedReplaced, segmentId)
    return dualsReplaced
  }
}
