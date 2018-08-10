function onTagMouseEnter (tagElement, parentId) {
  console.log(parentId)
  const elements = document.getElementById(parentId).querySelectorAll("[data-id='" + tagElement.getAttribute('data-id') + "']")
  for (let i = 0; i < elements.length; i++) {
    elements[i].classList.add('tag-bg')
  }
}

function onTagMouseLeave (tagElement, parentId) {
  console.log(parentId)
  const elements = document.getElementById(parentId).querySelectorAll("[data-id='" + tagElement.getAttribute('data-id') + "']")
  for (let i = 0; i < elements.length; i++) {
    elements[i].classList.remove('tag-bg')
  }
}
