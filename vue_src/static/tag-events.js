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
  caretPosition = getCaretPosition(activeSpan)
}

function onPress (e, element) {
  if (element.getAttribute('contenteditable') === 'false') {
    return
  }
  let keynum;

  if (window.event) {
    keynum = e.keyCode
  } else if (e.which){
    keynum = e.which
  }
  if (keynum !== 37 && keynum !== 39) {
    return
  }
  if (keynum === 37 && caretPosition === 0) {
    e.preventDefault()
    e.stopPropagation()
    previousSpan(element)
  }
  if (keynum === 39 && caretPosition === element.innerHTML.length) {
    e.preventDefault()
    e.stopPropagation()
    nextSpan(element)
  }
}

function previousSpan (element) {
  const nodes = element.parentElement.childNodes
  let activeNumber = -1
  for (let i = 0; i < nodes.length; i++) {
    if (nodes[i].className !== 'editor-span' || nodes[i].id !== activeEditorId) {
      continue
    }
    activeNumber = i
    break
  }
  let previousSpan = null
  for (let i = 0; i < nodes.length; i++) {
    if (nodes[i].className !== 'editor-span') {
      continue
    }
    if (i === activeNumber) {
      break
    }
    previousSpan = nodes[i]
  }
  if (previousSpan !== null) {
    previousSpan.focus()
    setEndOfContenteditable(previousSpan)
  }
}

function setEndOfContenteditable(contentEditableElement)
{
  var range,selection;
  if(document.createRange)//Firefox, Chrome, Opera, Safari, IE 9+
  {
    range = document.createRange();//Create a range (a range is a like the selection but invisible)
    range.selectNodeContents(contentEditableElement);//Select the entire contents of the element with the range
    range.collapse(false);//collapse the range to the end point. false means collapse to end rather than the start
    selection = window.getSelection();//get the selection object (allows you to change selection)
    selection.removeAllRanges();//remove any selections already made
    selection.addRange(range);//make the range you have just created the visible selection
  }
  else if(document.selection)//IE 8 and lower
  {
    range = document.body.createTextRange();//Create a range (a range is a like the selection but invisible)
    range.moveToElementText(contentEditableElement);//Select the entire contents of the element with the range
    range.collapse(false);//collapse the range to the end point. false means collapse to end rather than the start
    range.select();//Select the range (make it the visible selection
  }
}

function nextSpan (element) {
  let activeFound = false
  const nodes = element.parentElement.childNodes
  for (let i = 0; i < nodes.length; i++) {
    if (nodes[i].className !== 'editor-span') {
      continue
    }
    if (activeFound) {
      nodes[i].focus()
      break
    }
    if (nodes[i].id !== activeEditorId) {
      continue
    }
    activeFound = true
  }
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
}

function getAbsoluteCaretPos () {
  const searchString = 'id="' + activeEditorId + '">'
  const indexOf = activeSpan.parentElement.innerHTML.indexOf(searchString)
  return indexOf + searchString.length + caretPosition
}
