// TODO How to handle xlif ids of _48 and sorts?
// TODO Make global mousenter/leave listener
import {
  DuplicateTagInSource,
  InvalidXlifTags,
  ValueMissing
} from './exceptions'
import {
  DualCloseTag,
  DualOpenTag,
  SelfClosingTag
} from './segment-tag'

const selfClosingPattern = /&lt;.?.?.?.?.? id="[0-9]+"\/&gt;/g
const dualOpenPattern = /&lt;.?.?.?.?.? id="[0-9]+"&gt;/
const dualClosePattern = /&lt;\/.?.?.?.?.?&gt;/

export function xliffToHtml (text, segmentId) {
  const selfClosedReplaced = replaceAllSelfClosedTags(text, segmentId)
  return replaceAllDualsTags(selfClosedReplaced, segmentId)
}
export function htmlToXliff (text) {
  return ''
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
    outputText = replaceOneSelfClosedTag(foundTag, outputText, segmentId)
  })
  return outputText
}
export function replaceOneSelfClosedTag (xliffTag, text, segmentId) {
  const tagCount = (text.match(new RegExp(xliffTag, 'g')) || []).length
  if (tagCount > 1) {
    throw new DuplicateTagInSource(text)
  }
  const tag = new SelfClosingTag(xliffTag, segmentId)
  return text.replace(xliffTag, tag.toHtml())
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
function replaceOpenTag (text, xliffTag, segmentId) {
  const tag = new DualOpenTag(xliffTag, segmentId)
  return text.replace(tag, tag.toHtml())
}
function replaceCloseTag (text, xliffCloseTag, openTag) {
  const closeTag = new DualCloseTag(openTag)
  return text.replace(closeTag, closeTag.toHtml())
}
