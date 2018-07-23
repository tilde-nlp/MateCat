const tagStart = '<search-span style="background-color: yellow;">'
const tagEnd = '</search-span>'
export const TextHighlighter = {
  init: function () {
    document.registerElement('search-span')
  },
  add: function (search, text) {
    let termPosition = text.toLowerCase().indexOf(search)
    while (termPosition > -1) {
      text = [text.slice(0, termPosition), tagStart, text.slice(termPosition)].join('')
      const end = termPosition + search.length + tagStart.length
      text = [text.slice(0, end), tagEnd, text.slice(end)].join('')
      termPosition = text.toLowerCase().indexOf(search, end + tagEnd.length)
    }
    return text
  },
  remove: function (text) {
    let result = text.replace(new RegExp(tagStart, 'g'), '')
    return result.replace(new RegExp(tagEnd, 'g'), '')
  }
}
