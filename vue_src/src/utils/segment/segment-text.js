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
const dualOpenXliffPatternAll = /&lt;.?.?.?.?.? id="[0-9]+"&gt;/g
const dualCloseXliffPattern = /&lt;\/(?!span).?.?.?.?.?&gt;/
const dualCloseXliffPatternAll = /&lt;\/(?!span).?.?.?.?.?&gt;/g
const selfClosingHtmlPattern = /<\/span><span class="tag self-closing" data-tag-name=".?.?.?.?.?-sc" data-xlif-id="[0-9]+" data-class-id="tag-[0-9]+-[0-9]+" onmouseenter="onTagMouseEnter\(this\)" onmouseleave="onTagMouseLeave\(this\)">.?.?.?.?.?<\/span><span class="editor-span" contenteditable="(true|false)">/g
const dualOpenHtmlPattern = /<\/span><span class="tag dual-open" data-tag-name=".?.?.?.?.?-do" data-xlif-id="[0-9]+" data-class-id="tag-[0-9]+-[0-9]+" onmouseenter="onTagMouseEnter\(this\)" onmouseleave="onTagMouseLeave\(this\)">.?.?.?.?.?<\/span><span class="editor-span" contenteditable="(true|false)">/g
const dualCloseHtmlPattern = /<\/span><span class="tag dual-close" data-tag-name=".?.?.?.?.?-dc" data-xlif-id="[0-9]+" data-class-id="tag-[0-9]+-[0-9]+" onmouseenter="onTagMouseEnter\(this\)" onmouseleave="onTagMouseLeave\(this\)">.?.?.?.?.?<\/span><span class="editor-span" contenteditable="(true|false)">/g
const editorStart = '<span class="editor-span" contenteditable="false">'
const editorEnd = '</span>'

export function xliffToHtml (inputText, segmentId) {
  if (typeof (inputText) === 'undefined' || inputText === null) {
    throw new ValueMissing()
  }
  inputText = editorStart + inputText + editorEnd
  const selfClosedReplaced = replaceAllXliffSelfClosedTags(inputText, segmentId)
  return replaceAllXliffDualTags(selfClosedReplaced, segmentId)
}
export function htmlToXliff (inputText, segmentId) {
  if (typeof (inputText) === 'undefined' || inputText === null) {
    throw new ValueMissing()
  }
  inputText = inputText.substr(editorStart.length - 1)
  inputText = inputText.slice(0, -1 * editorEnd.length)
  const selfClosedReplaced = replaceAllHtmlSelfClosedTags(inputText, segmentId)
  return replaceAllHtmlDualTags(selfClosedReplaced, segmentId)
}
export function getTagList (inputText, segmentId) {
  if (typeof (inputText) === 'undefined' || inputText === null) {
    throw new ValueMissing()
  }
  const selfClosedResponse = findAllXliffSelfClosedTags(inputText, segmentId)
  let tags = selfClosedResponse.tags
  const dualResponse = findAllXliffDualTags(selfClosedResponse.text, segmentId)
  tags = tags.concat(dualResponse.tags)
  return tags
}
export function stripXliffTags (inputText) {
  console.log(inputText)
  const selfClosingMatches = inputText.match(selfClosingXliffPattern)
  if (selfClosingMatches !== null) {
    for (let i = 0; i < selfClosingMatches.length; i++) {
      inputText = inputText.replace(selfClosingMatches[i], '')
    }
  }
  const dualOpenMatches = inputText.match(dualOpenXliffPatternAll)
  console.log(dualOpenMatches)
  if (dualOpenMatches !== null) {
    for (let i = 0; i < dualOpenMatches.length; i++) {
      console.log('replacing: ' + dualOpenMatches[i])
      inputText = inputText.replace(dualOpenMatches[i], '')
    }
  }
  const dualCloseMatches = inputText.match(dualCloseXliffPatternAll)
  if (dualCloseMatches !== null) {
    for (let i = 0; i < dualCloseMatches.length; i++) {
      inputText = inputText.replace(dualCloseMatches[i], '')
    }
  }
  return inputText
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
export function findAllXliffSelfClosedTags (inputText, segmentId) {
  const matches = inputText.match(selfClosingXliffPattern)
  if (matches === null) {
    return {text: inputText, tags: []}
  }
  let outputText = inputText
  let tags = []
  matches.forEach(foundTag => {
    const selfClosingTag = new SelfClosingTag()
    selfClosingTag.fromXliff(foundTag)
    selfClosingTag.segmentId = segmentId
    outputText = selfClosingTag.replaceToHtml(outputText)
    tags.push(selfClosingTag)
  })
  return {text: outputText, tags: tags}
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
export function findAllXliffDualTags (inputText, segmentId) {
  let outputText = inputText
  let openTagPosition = outputText.search(dualOpenXliffPattern)
  let closeTagPosition = outputText.search(dualCloseXliffPattern)
  if (openTagPosition < 0 && closeTagPosition >= 0) {
    throw InvalidXlifTags
  }
  if (openTagPosition < 0) {
    return {text: outputText, tags: []}
  }
  let stack = []
  let counter = 0
  let tags = []
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
      tags.push(dualOpenTag)
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
  return {text: outputText, tags: tags}
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
