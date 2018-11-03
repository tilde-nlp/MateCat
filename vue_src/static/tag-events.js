var activeSpan = null
var caretPosition = 0
var ie = (typeof document.selection !== 'undefined' && document.selection.type !== 'Control') && true
var w3 = (typeof window.getSelection !== 'undefined') && true
var activeEditorId = 'active-editor-span'

function onTagMouseEnter (tagElement) {
  const elements = document.querySelectorAll("[data-class-id='" + tagElement.getAttribute('data-class-id') + "']")
  for (let i = 0; i < elements.length; i++) {
    elements[i].classList.add('active')
  }
}

function onTagMouseLeave (tagElement) {
  const elements = document.querySelectorAll("[data-class-id='" + tagElement.getAttribute('data-class-id') + "']")
  for (let i = 0; i < elements.length; i++) {
    elements[i].classList.remove('active')
  }
}

function onEditor (element) {
  if (element.getAttribute('contenteditable') === 'false') {
    return
  }
  const currentActive = document.getElementById(activeEditorId)
  if (currentActive != null) {
    currentActive.removeAttribute('id')
  }
  activeSpan = element
  activeSpan.id = activeEditorId
  // console.log('Active span: ')
  // console.log(activeSpan)
  caretPosition = getCaretPosition(activeSpan)
  // console.log('saving caret: ' + caretPosition)
}

function getCaretPosition () {
  try {
    if (w3) {
      let range = window.getSelection().getRangeAt(0)
      let preCaretRange = range.cloneRange()
      preCaretRange.selectNodeContents(activeSpan)
      preCaretRange.setEnd(range.endContainer, range.endOffset)
      return preCaretRange.toString().length
    }
    if (ie) {
      let textRange = document.selection.createRange()
      let preCaretTextRange = document.body.createTextRange()
      preCaretTextRange.moveToElementText(activeSpan)
      preCaretTextRange.setEndPoint('EndToEnd', textRange)
      return preCaretTextRange.text.length
    }
  } catch (e) {
    return 0
  }
  return 0
}

function insertHtmlAtCaret (text) {
  let absoluteCaret = getAbsoluteCaretPos()
  let fullText = activeSpan.parentElement.innerHTML
  activeSpan.parentElement.innerHTML = fullText.slice(0, absoluteCaret) + text + fullText.slice(absoluteCaret)
  console.log('inserted')
  console.log(activeSpan.parentElement)
}

function getAbsoluteCaretPos () {
  const searchString = 'id="' + activeEditorId + '">'
  const indexOf = activeSpan.parentElement.innerHTML.indexOf(searchString)
  return indexOf + searchString.length + caretPosition
}
