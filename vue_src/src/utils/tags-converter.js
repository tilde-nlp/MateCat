const rawGTagSearch = '&lt;g id="'
const convertedGTagSearch = '<g-span data-id="'
const editorTagStart = '<span class="editor-span">'
const editorTagStartEditable = '<span class="editor-span" contenteditable="true">'
const editorTagEnd = '</span>'
function processInnerTag (start, text) {
  if (start === 0) {
    text = editorTagStart + text + editorTagEnd
  }
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
  return text
}
function getGStartTagC (id) {
  return editorTagEnd + '<g-span data-id="' + id + '" class="bg-blueish">&gt;</g-span>' + editorTagStart
}
function getGEndTagC (id) {
  return editorTagEnd + '<g-span data-id="' + id + '" class="bg-blueish">&lt;</g-span>' + editorTagStart
}
function getGStartTagCE (id) {
  return editorTagEnd + '<g-span data-id="' + id + '" class="bg-blueish">&gt;</g-span>' + editorTagStartEditable
}
function getGEndTagCE (id) {
  return editorTagEnd + '<g-span data-id="' + id + '" class="bg-blueish">&lt;</g-span>' + editorTagStartEditable
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
  },
  add: function (text) {
    return processInnerTag(0, text)
  },
  remove: function (text) {
    console.log('remove')
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
    text = text.replace(new RegExp(editorTagStartEditable, 'g'), '')
    text = text.replace(new RegExp(editorTagEnd, 'g'), '')
    text = text.replace(new RegExp('<br>', 'g'), '')
    return text
  }
}
