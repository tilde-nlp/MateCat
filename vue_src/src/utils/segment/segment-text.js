// TODO How to handle xlif ids of _48 and sorts?
// TODO Make global mousenter/leave listener
import {
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

export function xliffToHtml (inputText, segmentId) {
  if (typeof (inputText) === 'undefined' || inputText === null) {
    throw new ValueMissing()
  }
  const selfClosedReplaced = replaceAllXliffSelfClosedTags(inputText, segmentId)
  return replaceAllXliffDualTags(selfClosedReplaced, segmentId)
}
export function htmlToXliff (text) {
  return ''
}
export function replaceAllXliffSelfClosedTags (inputText, segmentId) {
  const matches = inputText.match(selfClosingPattern)
  if (matches === null) {
    return inputText
  }
  let outputText = inputText
  matches.forEach(foundTag => {
    const selfClosingTag = new SelfClosingTag()
    selfClosingTag.fromXliff(foundTag)
    selfClosingTag.segmentId = segmentId
    outputText = selfClosingTag.replaceToHtml(outputText)
  })
  return outputText
}
export function replaceAllXliffDualTags (inputText, segmentId) {
  let outputText = inputText
  let openTagPosition = outputText.search(dualOpenPattern)
  let closeTagPosition = outputText.search(dualClosePattern)
  if (openTagPosition < 0 && closeTagPosition >= 0) {
    throw InvalidXlifTags
  }
  if (openTagPosition < 0) {
    return outputText
  }
  let stack = []
  let counter = 0
  while (1) {
    counter++
    if (counter > 300) {
      break
    }
    let openTagPosition = outputText.search(dualOpenPattern)
    let closeTagPosition = outputText.search(dualClosePattern)
    if (openTagPosition < 0 && closeTagPosition < 0) {
      break
    }
    if (openTagPosition > -1 && openTagPosition < closeTagPosition) {
      const xliffOpenTag = outputText.match(dualOpenPattern)[0]
      const dualOpenTag = new DualOpenTag()
      dualOpenTag.fromXliff(xliffOpenTag)
      dualOpenTag.segmentId = segmentId
      outputText = dualOpenTag.replaceToHtml(outputText)
      stack.push(dualOpenTag)
    } else {
      const dualOpenTag = stack.pop()
      const dualCloseTag = new DualCloseTag()
      dualCloseTag.id = dualOpenTag.id
      dualCloseTag.name = dualOpenTag.name
      dualCloseTag.segmentId = dualOpenTag.segmentId
      outputText = dualCloseTag.replaceToHtml(outputText)
    }
  }
  if (stack.length > 0) {
    throw InvalidXlifTags
  }
  return outputText
}
