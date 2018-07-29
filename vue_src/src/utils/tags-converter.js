import {CONFIG} from '../CONFIG'
const rawGTagSearch = '&lt;g id="'
const rawXTagSearch = '&lt;x id="'
const rawBxTagSearch = '&lt;bx id="'
const rawExTagSearch = '&lt;ex id="'
const convertedGTagSearch = '<g-span data-id="'
const convertedXTagSearch = '<x-span data-id="'
const convertedBxTagSearch = '<bx-span data-id="'
const convertedExTagSearch = '<ex-span data-id="'
const editorTagStart = '<span class="editor-span">'
const editorTagStartEditable = '<span class="editor-span" contenteditable="true">'
const editorTagEnd = '</span>'
function processInnerTag (start, text) {
  if (start === 0) {
    text = editorTagStart + text + editorTagEnd
  }
  // Process G tag
  let gTagPosition = text.indexOf(rawGTagSearch)
  while (gTagPosition > -1) {
    // Find out tag id
    const closingMark = text.indexOf('"', gTagPosition + rawGTagSearch.length)
    const id = parseInt(text.substring(gTagPosition + rawGTagSearch.length, closingMark))
    text = text.replace(getGStartTagR(id), getGStartTagC(id))
    const endTagPos = text.indexOf(getGEndTagR())
    const nextTagStartPos = text.indexOf(rawGTagSearch)
    if (nextTagStartPos > -1 && endTagPos > nextTagStartPos) {
      text = processInnerTag(nextTagStartPos, text)
    }
    text = text.replace(getGEndTagR(), getGEndTagC(id))
    gTagPosition = text.indexOf(rawGTagSearch, endTagPos + getGEndTagC(id).length)
  }
  // Process X tag
  let xTagPosition = text.indexOf(rawXTagSearch)
  while (xTagPosition > -1) {
    // Find out tag id
    const closingMark = text.indexOf('"', xTagPosition + rawXTagSearch.length)
    const id = parseInt(text.substring(xTagPosition + rawXTagSearch.length, closingMark))
    text = text.replace(getXTagR(id), getXTagC(id))
    const endPos = xTagPosition + getXTagC(id).length
    xTagPosition = text.indexOf(rawXTagSearch, endPos)
  }
  // Process BX tag
  let bxTagPosition = text.indexOf(rawBxTagSearch)
  while (bxTagPosition > -1) {
    // Find out tag id
    const closingMark = text.indexOf('"', bxTagPosition + rawBxTagSearch.length)
    const id = parseInt(text.substring(bxTagPosition + rawBxTagSearch.length, closingMark))
    text = text.replace(getBxTagR(id), getBxTagC(id))
    const endPos = bxTagPosition + getBxTagC(id).length
    bxTagPosition = text.indexOf(rawBxTagSearch, endPos)
  }
  // Process EX tag
  let exTagPosition = text.indexOf(rawExTagSearch)
  while (exTagPosition > -1) {
    // Find out tag id
    const closingMark = text.indexOf('"', exTagPosition + rawExTagSearch.length)
    const id = parseInt(text.substring(exTagPosition + rawExTagSearch.length, closingMark))
    text = text.replace(getExTagR(id), getExTagC(id))
    const endPos = exTagPosition + getExTagC(id).length
    exTagPosition = text.indexOf(rawExTagSearch, endPos)
  }
  return text
}
function getXTagC (id) {
  return editorTagEnd + '<x-span data-id="' + id + '"><img src="' + CONFIG.assetPath + 'x-tag.svg" height="24" class="va-middle ib"></x-span>' + editorTagStart
}
function getXTagR (id) {
  return rawXTagSearch + id + '"/&gt;'
}
function getXTagCE (id) {
  return editorTagEnd + '<x-span data-id="' + id + '"><img src="' + CONFIG.assetPath + 'x-tag.svg" height="24" class="va-middle ib"></x-span>' + editorTagStartEditable
}
function getBxTagC (id) {
  return editorTagEnd + '<bx-span data-id="' + id + '"><img src="' + CONFIG.assetPath + 'x-tag.svg" height="24" class="va-middle ib"></bx-span>' + editorTagStart
}
function getBxTagR (id) {
  return rawBxTagSearch + id + '"/&gt;'
}
function getBxTagCE (id) {
  return editorTagEnd + '<bx-span data-id="' + id + '"><img src="' + CONFIG.assetPath + 'x-tag.svg" height="24" class="va-middle ib"></bx-span>' + editorTagStartEditable
}
function getExTagC (id) {
  return editorTagEnd + '<ex-span data-id="' + id + '"><img src="' + CONFIG.assetPath + 'x-tag.svg" height="24" class="va-middle ib"></ex-span>' + editorTagStart
}
function getExTagR (id) {
  return rawExTagSearch + id + '"/&gt;'
}
function getExTagCE (id) {
  return editorTagEnd + '<ex-span data-id="' + id + '"><img src="' + CONFIG.assetPath + 'x-tag.svg" height="24" class="va-middle ib"></ex-span>' + editorTagStartEditable
}
function getGStartTagC (id) {
  return editorTagEnd + '<g-span data-id="' + id + '"><img src="' + CONFIG.assetPath + 'g-tag-open.svg" height="24" class="va-middle ib"></g-span>' + editorTagStart
}
function getGEndTagC (id) {
  return editorTagEnd + '<g-span data-id="' + id + '"><img src="' + CONFIG.assetPath + 'g-tag-close.svg" height="24" class="va-middle ib"></g-span>' + editorTagStart
}
function getGStartTagCE (id) {
  return editorTagEnd + '<g-span data-id="' + id + '"><img src="' + CONFIG.assetPath + 'g-tag-open.svg" height="24" class="va-middle ib"></g-span>' + editorTagStartEditable
}
function getGEndTagCE (id) {
  return editorTagEnd + '<g-span data-id="' + id + '"><img src="' + CONFIG.assetPath + 'g-tag-close.svg" height="24" class="va-middle ib"></g-span>' + editorTagStartEditable
}
function getGStartTagR (id) {
  return rawGTagSearch + id + '"&gt;'
}
function getGEndTagR () {
  return '&lt;/g&gt;'
}
export const TagsConverter = {
  init: function () {
    document.registerElement('g-span')
    document.registerElement('x-span')
    document.registerElement('bx-span')
    document.registerElement('ex-span')
  },
  add: function (text) {
    return processInnerTag(0, text)
  },
  remove: function (text) {
    // Replace G tags
    let gTagPosition = text.indexOf(convertedGTagSearch)
    let safetyCounter = 0
    while (gTagPosition > -1) {
      const closingMark = text.indexOf('"', gTagPosition + convertedGTagSearch.length)
      const id = parseInt(text.substring(gTagPosition + convertedGTagSearch.length, closingMark))
      text = text.replace(getGStartTagCE(id), getGStartTagR(id))
      text = text.replace(getGEndTagCE(id), getGEndTagR())
      gTagPosition = text.indexOf(convertedGTagSearch)
      safetyCounter++
      if (safetyCounter > 264) {
        alert('Too many tags converter iterations')
        gTagPosition = -1
      }
    }
    // Replace X tags
    let xTagPosition = text.indexOf(convertedXTagSearch)
    safetyCounter = 0
    while (xTagPosition > -1) {
      const closingMark = text.indexOf('"', xTagPosition + convertedXTagSearch.length)
      const id = parseInt(text.substring(xTagPosition + convertedXTagSearch.length, closingMark))
      text = text.replace(getXTagCE(id), getXTagR(id))
      xTagPosition = text.indexOf(convertedXTagSearch)
      safetyCounter++
      if (safetyCounter > 264) {
        alert('Too many tags converter iterations')
        xTagPosition = -1
      }
    }
    // Replace BX tags
    let bxTagPosition = text.indexOf(convertedBxTagSearch)
    safetyCounter = 0
    while (bxTagPosition > -1) {
      const closingMark = text.indexOf('"', bxTagPosition + convertedBxTagSearch.length)
      const id = parseInt(text.substring(bxTagPosition + convertedBxTagSearch.length, closingMark))
      text = text.replace(getBxTagCE(id), getBxTagR(id))
      bxTagPosition = text.indexOf(convertedBxTagSearch)
      safetyCounter++
      if (safetyCounter > 264) {
        alert('Too many tags converter iterations')
        bxTagPosition = -1
      }
    }
    // Replace EX tags
    let exTagPosition = text.indexOf(convertedExTagSearch)
    safetyCounter = 0
    while (exTagPosition > -1) {
      const closingMark = text.indexOf('"', exTagPosition + convertedExTagSearch.length)
      const id = parseInt(text.substring(exTagPosition + convertedExTagSearch.length, closingMark))
      text = text.replace(getExTagCE(id), getExTagR(id))
      exTagPosition = text.indexOf(convertedExTagSearch)
      safetyCounter++
      if (safetyCounter > 264) {
        alert('Too many tags converter iterations')
        exTagPosition = -1
      }
    }
    text = text.replace(new RegExp(editorTagStartEditable, 'g'), '')
    text = text.replace(new RegExp(editorTagEnd, 'g'), '')
    text = text.replace(new RegExp('<br>', 'g'), '')
    return text
  }
}
