// TODO How to handle xlif ids of _48 and sorts?
// TODO Make global mousenter/leave listener
import {
  InvalidXlifTags,
  InvalidHtmlTags,
  ValueMissing
} from './exceptions'
import {
  DualCloseTag,
  DualOpenTag,
  SelfClosingTag
} from './segment-tag'

const selfClosingXliffPattern = /&lt;.?.?.?.?.? id="[0-9]+"\/&gt;/g
const dualOpenXliffPattern = /&lt;.?.?.?.?.? id="[0-9]+"&gt;/
const dualCloseXliffPattern = /&lt;\/.?.?.?.?.?&gt;/
const selfClosingHtmlPattern = /<span contenteditable="false" class="tag self-closing" data-tag-name=".?.?.?.?.?-sc" data-xlif-id="[0-9]+" data-class-id="tag-[0-9]+-[0-9]+" onmouseenter="onTagMouseEnter\(this\)" onmouseleave="onTagMouseLeave\(this\)">.?.?.?.?.?<\/span>/g
const dualOpenHtmlPattern = /<span contenteditable="false" class="tag dual-open" data-tag-name=".?.?.?.?.?-do" data-xlif-id="[0-9]+" data-class-id="tag-[0-9]+-[0-9]+" onmouseenter="onTagMouseEnter\(this\)" onmouseleave="onTagMouseLeave\(this\)">.?.?.?.?.?<\/span>/g
const dualCloseHtmlPattern = /<span contenteditable="false" class="tag dual-close" data-tag-name=".?.?.?.?.?-dc" data-xlif-id="[0-9]+" data-class-id="tag-[0-9]+-[0-9]+" onmouseenter="onTagMouseEnter\(this\)" onmouseleave="onTagMouseLeave\(this\)">.?.?.?.?.?<\/span>/g

export function xliffToHtml (inputText, segmentId) {
  if (typeof (inputText) === 'undefined' || inputText === null) {
    throw new ValueMissing()
  }
  const selfClosedReplaced = replaceAllXliffSelfClosedTags(inputText, segmentId)
  return replaceAllXliffDualTags(selfClosedReplaced, segmentId)
}
export function htmlToXliff (inputText, segmentId) {
  if (typeof (inputText) === 'undefined' || inputText === null) {
    throw new ValueMissing()
  }
  const selfClosedReplaced = replaceAllHtmlSelfClosedTags(inputText, segmentId)
  return replaceAllHtmlDualTags(selfClosedReplaced, segmentId)
}
export function replaceAllXliffSelfClosedTags (inputText, segmentId) {
  const matches = inputText.match(selfClosingXliffPattern)
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
  let openTagPosition = outputText.search(dualOpenXliffPattern)
  let closeTagPosition = outputText.search(dualCloseXliffPattern)
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
    let openTagPosition = outputText.search(dualOpenXliffPattern)
    let closeTagPosition = outputText.search(dualCloseXliffPattern)
    if (openTagPosition < 0 && closeTagPosition < 0) {
      break
    }
    if (openTagPosition > -1 && openTagPosition < closeTagPosition) {
      const xliffOpenTag = outputText.match(dualOpenXliffPattern)[0]
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
export function replaceAllHtmlSelfClosedTags (inputText, segmentId) {
  const matches = inputText.match(selfClosingHtmlPattern)
  if (matches === null) {
    return inputText
  }
  let outputText = inputText
  matches.forEach(foundTag => {
    const selfClosingTag = new SelfClosingTag()
    selfClosingTag.fromHtml(foundTag)
    selfClosingTag.segmentId = segmentId
    outputText = selfClosingTag.replaceToXliff(outputText)
  })
  return outputText
}
export function replaceAllHtmlDualTags (inputText, segmentId) {
  let outputText = inputText
  let openTagPosition = outputText.search(dualOpenHtmlPattern)
  let closeTagPosition = outputText.search(dualCloseHtmlPattern)
  if (openTagPosition < 0 && closeTagPosition >= 0) {
    throw InvalidHtmlTags
  }
  if (openTagPosition < 0) {
    return outputText
  }
  let stack = []
  let counter = 0
  while (1) {
    counter++
    if (counter > 500) {
      break
    }
    let openTagPosition = outputText.search(dualOpenHtmlPattern)
    let closeTagPosition = outputText.search(dualCloseHtmlPattern)
    if (openTagPosition < 0 && closeTagPosition < 0) {
      break
    }
    if (openTagPosition > -1 && openTagPosition < closeTagPosition) {
      const htmlOpenTag = outputText.match(dualOpenHtmlPattern)[0]
      const dualOpenTag = new DualOpenTag()
      dualOpenTag.fromHtml(htmlOpenTag)
      dualOpenTag.segmentId = segmentId
      outputText = dualOpenTag.replaceToXliff(outputText)
      stack.push(dualOpenTag)
    } else {
      const dualOpenTag = stack.pop()
      const dualCloseTag = new DualCloseTag()
      dualCloseTag.id = dualOpenTag.id
      dualCloseTag.name = dualOpenTag.name
      dualCloseTag.segmentId = dualOpenTag.segmentId
      outputText = dualCloseTag.replaceToXliff(outputText)
    }
  }
  if (stack.length > 0) {
    throw InvalidHtmlTags
  }
  return outputText
}
