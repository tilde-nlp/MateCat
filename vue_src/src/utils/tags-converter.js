import {CONFIG} from '../CONFIG'
const rawGTagSearch = '&lt;g id="'
const rawSCGTagSearch = '"/&gt;'
const rawXTagSearch = '&lt;x id="'
const rawBxTagSearch = '&lt;bx id="'
const rawExTagSearch = '&lt;ex id="'
const convertedGTagSearch = '<g-span data-id="'
const convertedXTagSearch = '<x-span data-id="'
const convertedBxTagSearch = '<bx-span data-id="'
const convertedExTagSearch = '<ex-span data-id="'
const convertedScgTagSearch = '<scg-span data-id="'
const editorTagStart = '<span class="editor-span">'
const editorTagStartEditable = '<span class="editor-span" contenteditable="true">'
const editorTagEnd = '</span>'
function processInnerTag (start, text, parentId) {
  if (start === 0) {
    text = editorTagStart + text + editorTagEnd
  }
  // Process X tag
  let xTagPosition = text.indexOf(rawXTagSearch)
  while (xTagPosition > -1) {
    // Find out tag id
    const closingMark = text.indexOf('"', xTagPosition + rawXTagSearch.length)
    const id = parseInt(text.substring(xTagPosition + rawXTagSearch.length, closingMark))
    text = text.replace(getXTagR(id), getXTagC(id, parentId))
    const endPos = xTagPosition + getXTagC(id, parentId).length
    xTagPosition = text.indexOf(rawXTagSearch, endPos)
  }
  // Process BX tag
  let bxTagPosition = text.indexOf(rawBxTagSearch)
  while (bxTagPosition > -1) {
    // Find out tag id
    const closingMark = text.indexOf('"', bxTagPosition + rawBxTagSearch.length)
    const id = parseInt(text.substring(bxTagPosition + rawBxTagSearch.length, closingMark))
    text = text.replace(getBxTagR(id), getBxTagC(id, parentId))
    const endPos = bxTagPosition + getBxTagC(id, parentId).length
    bxTagPosition = text.indexOf(rawBxTagSearch, endPos)
  }
  // Process EX tag
  let exTagPosition = text.indexOf(rawExTagSearch)
  while (exTagPosition > -1) {
    // Find out tag id
    const closingMark = text.indexOf('"', exTagPosition + rawExTagSearch.length)
    const id = parseInt(text.substring(exTagPosition + rawExTagSearch.length, closingMark))
    text = text.replace(getExTagR(id), getExTagC(id, parentId))
    const endPos = exTagPosition + getExTagC(id, parentId).length
    exTagPosition = text.indexOf(rawExTagSearch, endPos)
  }

  // Process self closing G tag
  let scgTagPosition = text.indexOf(rawGTagSearch)
  while (scgTagPosition > -1) {
    // Find out tag id
    const closingMark = text.indexOf('"', scgTagPosition + rawGTagSearch.length)
    const selfClosePos = text.indexOf(rawSCGTagSearch, closingMark)
    if (selfClosePos > closingMark) {
      scgTagPosition = text.indexOf(rawGTagSearch, closingMark)
      continue
    }
    const id = parseInt(text.substring(scgTagPosition + rawGTagSearch.length, closingMark))
    text = text.replace(getScgTagR(id), getScgTagC(id, parentId))
    const endPos = scgTagPosition + getScgTagC(id, parentId).length
    scgTagPosition = text.indexOf(rawGTagSearch, endPos)
  }

  // Process G tag
  let gTagPosition = text.indexOf(rawGTagSearch)
  while (gTagPosition > -1) {
    // Find out tag id
    const closingMark = text.indexOf('"', gTagPosition + rawGTagSearch.length)
    const id = parseInt(text.substring(gTagPosition + rawGTagSearch.length, closingMark))
    text = text.replace(getGStartTagR(id), getGStartTagC(id, parentId))
    let endTagPos = text.indexOf(getGEndTagR())
    const nextTagStartPos = text.indexOf(rawGTagSearch)
    if (nextTagStartPos > -1 && endTagPos > nextTagStartPos) {
      text = processInnerTag(nextTagStartPos, text, parentId)
      endTagPos = text.indexOf(getGEndTagR())
    }
    text = text.replace(getGEndTagR(), getGEndTagC(id, parentId))
    gTagPosition = text.indexOf(rawGTagSearch, endTagPos + getGEndTagC(id, parentId).length)
    const endTagPosCheck = text.indexOf(getGEndTagR())
    if (gTagPosition > endTagPosCheck) {
      return text
    }
  }
  return text
}
function getXTagC (id, parentId) {
  return editorTagEnd + '<x-span data-id="' + id + '" title="' + id + '" title="' + id + '" class="pointer" onmouseenter="onTagMouseEnter(this, \'' + parentId + '\')" onmouseleave="onTagMouseLeave(this, \'' + parentId + '\')"><img src="' + CONFIG.assetPath + 'x-tag.svg" height="16" class="va-middle ib"></x-span>' + editorTagStart
}
function getXTagR (id) {
  return rawXTagSearch + id + '"/&gt;'
}
function getXTagCE (id, parentId) {
  return editorTagEnd + '<x-span data-id="' + id + '" title="' + id + '" title="' + id + '" class="pointer" onmouseenter="onTagMouseEnter(this, \'' + parentId + '\')" onmouseleave="onTagMouseLeave(this, \'' + parentId + '\')"><img src="' + CONFIG.assetPath + 'x-tag.svg" height="16" class="va-middle ib"></x-span>' + editorTagStartEditable
}
function getBxTagC (id, parentId) {
  return editorTagEnd + '<bx-span data-id="' + id + '" title="' + id + '" class="pointer" onmouseenter="onTagMouseEnter(this, \'' + parentId + '\')" onmouseleave="onTagMouseLeave(this, \'' + parentId + '\')"><img src="' + CONFIG.assetPath + 'x-tag.svg" height="16" class="va-middle ib"></bx-span>' + editorTagStart
}
function getBxTagR (id) {
  return rawBxTagSearch + id + '"/&gt;'
}
function getBxTagCE (id, parentId) {
  return editorTagEnd + '<bx-span data-id="' + id + '" title="' + id + '" class="pointer" onmouseenter="onTagMouseEnter(this, \'' + parentId + '\')" onmouseleave="onTagMouseLeave(this, \'' + parentId + '\')"><img src="' + CONFIG.assetPath + 'x-tag.svg" height="16" class="va-middle ib"></bx-span>' + editorTagStartEditable
}
function getExTagC (id, parentId) {
  return editorTagEnd + '<ex-span data-id="' + id + '" title="' + id + '" class="pointer" onmouseenter="onTagMouseEnter(this, \'' + parentId + '\')" onmouseleave="onTagMouseLeave(this, \'' + parentId + '\')"><img src="' + CONFIG.assetPath + 'x-tag.svg" height="16" class="va-middle ib"></ex-span>' + editorTagStart
}
function getExTagR (id) {
  return rawExTagSearch + id + '"/&gt;'
}
function getExTagCE (id, parentId) {
  return editorTagEnd + '<ex-span data-id="' + id + '" title="' + id + '" class="pointer" onmouseenter="onTagMouseEnter(this, \'' + parentId + '\')" onmouseleave="onTagMouseLeave(this, \'' + parentId + '\')"><img src="' + CONFIG.assetPath + 'x-tag.svg" height="16" class="va-middle ib"></ex-span>' + editorTagStartEditable
}
function getGStartTagC (id, parentId) {
  return editorTagEnd + '<g-span data-id="' + id + '" title="' + id + '" class="pointer" onmouseenter="onTagMouseEnter(this, \'' + parentId + '\')" onmouseleave="onTagMouseLeave(this, \'' + parentId + '\')"><img src="' + CONFIG.assetPath + 'g-tag-open.svg" height="16" class="va-middle ib"></g-span>' + editorTagStart
}
function getGEndTagC (id, parentId) {
  return editorTagEnd + '<g-span data-id="' + id + '" title="' + id + '" class="pointer" onmouseenter="onTagMouseEnter(this, \'' + parentId + '\')" onmouseleave="onTagMouseLeave(this, \'' + parentId + '\')"><img src="' + CONFIG.assetPath + 'g-tag-close.svg" height="16" class="va-middle ib"></g-span>' + editorTagStart
}
function getGStartTagCE (id, parentId) {
  return editorTagEnd + '<g-span data-id="' + id + '" title="' + id + '" class="pointer" onmouseenter="onTagMouseEnter(this, \'' + parentId + '\')" onmouseleave="onTagMouseLeave(this, \'' + parentId + '\')"><img src="' + CONFIG.assetPath + 'g-tag-open.svg" height="16" class="va-middle ib"></g-span>' + editorTagStartEditable
}
function getGEndTagCE (id, parentId) {
  return editorTagEnd + '<g-span data-id="' + id + '" title="' + id + '" class="pointer" onmouseenter="onTagMouseEnter(this, \'' + parentId + '\')" onmouseleave="onTagMouseLeave(this, \'' + parentId + '\')"><img src="' + CONFIG.assetPath + 'g-tag-close.svg" height="16" class="va-middle ib"></g-span>' + editorTagStartEditable
}
function getGStartTagR (id) {
  return rawGTagSearch + id + '"&gt;'
}
function getGEndTagR () {
  return '&lt;/g&gt;'
}
function getScgTagC (id, parentId) {
  return editorTagEnd + '<scg-span data-id="' + id + '" title="' + id + '" class="pointer" onmouseenter="onTagMouseEnter(this, \'' + parentId + '\')" onmouseleave="onTagMouseLeave(this, \'' + parentId + '\')"><img src="' + CONFIG.assetPath + 'x-tag.svg" height="16" class="va-middle ib"></scg-span>' + editorTagStart
}
function getScgTagCE (id, parentId) {
  return editorTagEnd + '<scg-span data-id="' + id + '" title="' + id + '" class="pointer" onmouseenter="onTagMouseEnter(this, \'' + parentId + '\')" onmouseleave="onTagMouseLeave(this, \'' + parentId + '\')"><img src="' + CONFIG.assetPath + 'x-tag.svg" height="16" class="va-middle ib"></scg-span>' + editorTagStartEditable
}
function getScgTagR (id) {
  return rawGTagSearch + id + '"/&gt;'
}
export const TagsConverter = {
  init: function () {
    document.registerElement('g-span')
    document.registerElement('x-span')
    document.registerElement('bx-span')
    document.registerElement('ex-span')
    document.registerElement('scg-span')
  },
  add: function (text, parentId) {
    return processInnerTag(0, text, parentId)
  },
  remove: function (text, parentId) {
    // Replace G tags
    let gTagPosition = text.indexOf(convertedGTagSearch)
    let safetyCounter = 0
    while (gTagPosition > -1) {
      const closingMark = text.indexOf('"', gTagPosition + convertedGTagSearch.length)
      const id = parseInt(text.substring(gTagPosition + convertedGTagSearch.length, closingMark))
      text = text.replace(getGStartTagCE(id, parentId), getGStartTagR(id))
      text = text.replace(getGEndTagCE(id, parentId), getGEndTagR())
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
      text = text.replace(getXTagCE(id, parentId), getXTagR(id))
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
      text = text.replace(getBxTagCE(id, parentId), getBxTagR(id))
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
      text = text.replace(getExTagCE(id, parentId), getExTagR(id))
      exTagPosition = text.indexOf(convertedExTagSearch)
      safetyCounter++
      if (safetyCounter > 264) {
        alert('Too many tags converter iterations')
        exTagPosition = -1
      }
    }
    // Replace self closing G tags
    let scgTagPosition = text.indexOf(convertedScgTagSearch)
    safetyCounter = 0
    while (scgTagPosition > -1) {
      const closingMark = text.indexOf('"', scgTagPosition + convertedScgTagSearch.length)
      const id = parseInt(text.substring(scgTagPosition + convertedScgTagSearch.length, closingMark))
      text = text.replace(getScgTagCE(id, parentId), getScgTagR(id))
      scgTagPosition = text.indexOf(convertedScgTagSearch)
      safetyCounter++
      if (safetyCounter > 264) {
        alert('Too many tags converter iterations')
        scgTagPosition = -1
      }
    }
    text = text.replace(new RegExp(editorTagStartEditable, 'g'), '')
    text = text.replace(new RegExp(editorTagEnd, 'g'), '')
    text = text.replace(new RegExp('<br>', 'g'), '')
    return text
  },
  getTagList: function (text) {
    let tags = []
    // Process X tag
    let xTagPosition = text.indexOf(rawXTagSearch)
    while (xTagPosition > -1) {
      // Find out tag id
      const closingMark = text.indexOf('"', xTagPosition + rawXTagSearch.length)
      const id = parseInt(text.substring(xTagPosition + rawXTagSearch.length, closingMark))
      tags.push({
        type: 'x',
        id: id
      })
      xTagPosition = text.indexOf(rawXTagSearch, closingMark)
    }
    // Process BX tag
    let bxTagPosition = text.indexOf(rawBxTagSearch)
    while (bxTagPosition > -1) {
      // Find out tag id
      const closingMark = text.indexOf('"', bxTagPosition + rawBxTagSearch.length)
      const id = parseInt(text.substring(bxTagPosition + rawBxTagSearch.length, closingMark))
      tags.push({
        type: 'bx',
        id: id
      })
      bxTagPosition = text.indexOf(rawBxTagSearch, closingMark)
    }
    // Process EX tag
    let exTagPosition = text.indexOf(rawExTagSearch)
    while (exTagPosition > -1) {
      // Find out tag id
      const closingMark = text.indexOf('"', exTagPosition + rawExTagSearch.length)
      const id = parseInt(text.substring(exTagPosition + rawExTagSearch.length, closingMark))
      tags.push({
        type: 'ex',
        id: id
      })
      exTagPosition = text.indexOf(rawExTagSearch, closingMark)
    }

    // Process self closing G tag
    let scgTagPosition = text.indexOf(rawGTagSearch)
    while (scgTagPosition > -1) {
      // Find out tag id
      const closingMark = text.indexOf('"', scgTagPosition + rawGTagSearch.length)
      const selfClosePos = text.indexOf(rawSCGTagSearch, closingMark)
      if (selfClosePos > closingMark) {
        scgTagPosition = text.indexOf(rawGTagSearch, closingMark)
        continue
      }
      const id = parseInt(text.substring(scgTagPosition + rawGTagSearch.length, closingMark))
      tags.push({
        type: 'scg',
        id: id
      })
      scgTagPosition = text.indexOf(rawGTagSearch, closingMark)
    }

    // Process G tag
    let gTagPosition = text.indexOf(rawGTagSearch)
    while (gTagPosition > -1) {
      // Find out tag id
      const closingMark = text.indexOf('"', gTagPosition + rawGTagSearch.length)
      const selfClosePos = text.indexOf(rawSCGTagSearch, closingMark)
      if (selfClosePos <= closingMark) {
        scgTagPosition = text.indexOf(rawGTagSearch, closingMark)
        continue
      }
      const id = parseInt(text.substring(gTagPosition + rawGTagSearch.length, closingMark))
      tags.push({
        type: 'g',
        id: id
      })
      gTagPosition = text.indexOf(rawGTagSearch, closingMark)
    }
    return tags
  }
}
