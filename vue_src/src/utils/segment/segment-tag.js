import {
  ValueMissing,
  ValueEmpty,
  DuplicateTagInSource,
  InvalidXlifTags
} from './exceptions'

export class Tag {
  constructor () {
    this.id = ''
    this.name = ''
    this.segmentId = ''
    this.namePostfix = ''
    this.typeClass = ''
  }
  fromHtml (xliffTag) {
    this.id = getTagIdFromHtml(xliffTag)
    this.name = getTagNameFromHtml(xliffTag)
  }
  toHtml () {
    const template = '<span contenteditable="false" class="tag typeClass" data-tag-name="tagName-namePostfix" data-xlif-id="tagId" data-class-id="tag-tagId-segmentId" onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)">tagName</span>'
    let result = template.replace(new RegExp('tagName', 'g'), this.name)
    result = result.replace(new RegExp('namePostfix', 'g'), this.namePostfix)
    result = result.replace(new RegExp('tagId', 'g'), this.id)
    result = result.replace(new RegExp('segmentId', 'g'), this.segmentId)
    result = result.replace(new RegExp('typeClass', 'g'), this.typeClass)
    return result
  }
  fromXliff (xliffTag) {
    this.id = getTagIdFromXliff(xliffTag)
    this.name = getTagNameFromXliff(xliffTag)
  }
  toXliff () {
    return ''
  }
  replaceToHtml (source) {
    if (this instanceof SelfClosingTag || this instanceof DualOpenTag) {
      const tagCount = (source.match(new RegExp(this.escapeRegExp(this.toXliff()), 'g')) || []).length
      if (tagCount > 1) {
        throw new DuplicateTagInSource()
      }
    }
    return source.replace(this.toXliff(), this.toHtml())
  }
  replaceToXliff (source) {
    const tagCount = (source.match(new RegExp(this.escapeRegExp(this.toHtml()), 'g')) || []).length
    if (tagCount > 1) {
      throw new DuplicateTagInSource()
    }
    return source.replace(this.toHtml(), this.toXliff())
  }
  escapeRegExp (text) {
    return text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&')
  }
}
export class SelfClosingTag extends Tag {
  constructor () {
    super()
    this.namePostfix = 'sc'
    this.typeClass = 'self-closing'
  }
  toXliff () {
    return '&lt;' + this.name + ' id="' + this.id + '"/&gt;'
  }
  toFullHtml () {
    return this.toHtml()
  }
}
export class DualOpenTag extends Tag {
  constructor () {
    super()
    this.namePostfix = 'do'
    this.typeClass = 'dual-open'
  }
  toXliff () {
    return '&lt;' + this.name + ' id="' + this.id + '"&gt;'
  }
  toFullHtml () {
    let html = this.toHtml()
    const dualCloseTag = new DualCloseTag()
    dualCloseTag.id = this.id
    dualCloseTag.name = this.name
    dualCloseTag.segmentId = this.segmentId
    return html + dualCloseTag.toHtml()
  }
}
export class DualCloseTag extends Tag {
  constructor () {
    super()
    this.namePostfix = 'dc'
    this.typeClass = 'dual-close'
  }
  toXliff () {
    return '&lt;/' + this.name + '&gt;'
  }
}
export function getTagIdFromXliff (tag) {
  validateString(tag)
  const idStartPosition = tag.indexOf('"')
  const idEndPosition = tag.indexOf('"', idStartPosition + 1)
  return tag.substring(idStartPosition + 1, idEndPosition)
}
export function getTagNameFromXliff (tag) {
  validateString(tag)
  const spacePosition = tag.indexOf(' ')
  if (spacePosition > -1) {
    return tag.substring(4, spacePosition)
  }
  return getDualCloseTagNameFromXliff(tag)
}
export function getDualCloseTagNameFromXliff (tag) {
  const slashPosition = tag.indexOf('/')
  const ampersandPosition = tag.lastIndexOf('&')
  if (slashPosition < 0 || ampersandPosition < 0) {
    throw new InvalidXlifTags()
  }
  return tag.substring(slashPosition + 1, ampersandPosition)
}
export function getTagIdFromHtml (tag) {
  validateString(tag)
  const searchString = 'data-xlif-id="'
  const idStartPosition = tag.indexOf(searchString) + searchString.length
  const idEndPosition = tag.indexOf('"', idStartPosition)
  return tag.substring(idStartPosition, idEndPosition)
}
export function getTagNameFromHtml (tag) {
  validateString(tag)
  const searchString = 'data-tag-name="'
  const nameStartPosition = tag.indexOf(searchString) + searchString.length
  const nameEndPosition = tag.indexOf('-', nameStartPosition)
  return tag.substring(nameStartPosition, nameEndPosition)
}
export function validateString (text) {
  if (typeof (text) === 'undefined' || text === null) {
    throw new ValueMissing()
  }
  if (text === '') {
    throw new ValueEmpty()
  }
}
