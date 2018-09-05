import {RegisteredTags} from './registered-tags'
const selfClosingPattern = /&lt;.?.?.?.?.?id="[0-9]+"\/&gt;/g
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
  registerSelfClosedHtmlTag(htmlTagName)
  const htmlTag = buildHtmlTag(htmlTagName, id, segmentId)
  return text.replace(tag, htmlTag)
}
function getId (tag) {
  const idStartPosition = tag.indexOf('"')
  const idEndPosition = tag.indexOf('"', idStartPosition + 1)
  return parseInt(tag.substring(idStartPosition + 1, idEndPosition))
}
function getTagName (tag) {
  const spacePosition = tag.indexOf(' ')
  return tag.substring(4, spacePosition)
}
function registerSelfClosedHtmlTag (tagName) {
  if (RegisteredTags.indexOf(tagName) > -1) {
    return tagName
  }
  document.registerElement(tagName)
  RegisteredTags.push(tagName)
}
function buildHtmlTag (tagName, id, segmentId) {
  const classId = 'data-class-id="tag-' + id + '-' + segmentId + '"'
  const classList = ' class="tag self-closing"'
  const xlifId = ' data-xlif-id="' + id + '"'
  const events = 'onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)"'
  return '<' + tagName + classList + xlifId + classId + events + '>' + id + '</' + tagName + '>'
}
export const ToHtmlConverter = {
  convert: function (text, segmentId) {
    const selfClosedReplaced = replaceSelfClosed(text, segmentId)
    return selfClosedReplaced
  }
}
