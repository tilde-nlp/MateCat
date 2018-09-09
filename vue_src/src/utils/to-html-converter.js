// TODO How to handle xlif ids of _48 and sorts?
// TODO Make global mousenter/leave listener
import {InvalidXlifTags} from './exceptions/invalid-xlif-tags'
import {ValueMissing} from './exceptions/value-missing'
import {ValueEmpty} from './exceptions/value-empty'
const selfClosingPattern = /&lt;.?.?.?.?.? id="[0-9]+"\/&gt;/g
const dualOpenPattern = /&lt;.?.?.?.?.? id="[0-9]+"&gt;/
const dualClosePattern = /&lt;\/.?.?.?.?.?&gt;/
export class Tag {
  constructor (id, name, segmentId) {
    this.id = id
    this.name = name
    this.segmentId = segmentId
  }
}
export class SelfClosingTag extends Tag {
  constructor (tagString, segmentId) {
    super(getId(tagString), getTagName(tagString), segmentId)
    this.namePostfix = 'sc'
    this.typeClass = 'self-closing'
  }
}
export class DualOpenTag extends Tag {
  constructor (tagString, segmentId) {
    super(getId(tagString), getTagName(tagString), segmentId)
    this.namePostfix = 'do'
    this.typeClass = 'dual-open'
  }
}
export class DualCloseTag extends Tag {
  constructor (openTag) {
    super(openTag.id, openTag.name, openTag.segmentId)
    this.namePostfix = 'dc'
    this.typeClass = 'dual-close'
  }
}
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
  const htmlTag = buildHtmlTag(new SelfClosingTag(tag, segmentId))
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
      stack.push(new DualOpenTag(openTag, segmentId))
    } else {
      const closeTag = text.match(dualClosePattern)[0]
      const openTag = stack.pop()
      text = replaceCloseTag(text, closeTag, openTag)
    }
  }
  if (stack.length > 0) {
    throw InvalidXlifTags
  }
  return text
}
function replaceOpenTag (text, tag, segmentId) {
  const htmlTag = buildHtmlTag(new DualOpenTag(tag, segmentId))
  return text.replace(tag, htmlTag)
}
function replaceCloseTag (text, closeTag, openTag) {
  const htmlTag = buildHtmlTag(new DualCloseTag(openTag))
  return text.replace(closeTag, htmlTag)
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
export function buildHtmlTag (tag) {
  const template = '<span class="tag typeClass" data-tag-name="tagName-namePostfix" data-xlif-id="tagId" data-class-id="tag-tagId-segmentId" onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)">tagName</span>'
  let result = template.replace(new RegExp('tagName', 'g'), tag.name)
  result = result.replace(new RegExp('namePostfix', 'g'), tag.namePostfix)
  result = result.replace(new RegExp('tagId', 'g'), tag.id)
  result = result.replace(new RegExp('segmentId', 'g'), tag.segmentId)
  result = result.replace(new RegExp('typeClass', 'g'), tag.typeClass)
  return result
}
export const ToHtmlConverter = {
  convert: function (text, segmentId) {
    const selfClosedReplaced = replaceAllSelfClosedTags(text, segmentId)
    return replaceAllDualsTags(selfClosedReplaced, segmentId)
  }
}
