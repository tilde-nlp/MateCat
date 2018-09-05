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
