import {ValueMissing, ValueEmpty} from './exceptions'

export class Tag {
  constructor (id, name, segmentId) {
    this.id = id
    this.name = name
    this.segmentId = segmentId
    this.namePostfix = ''
    this.typeClass = ''
  }
  toHtml () {
    const template = '<span class="tag typeClass" data-tag-name="tagName-namePostfix" data-xlif-id="tagId" data-class-id="tag-tagId-segmentId" onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)">tagName</span>'
    let result = template.replace(new RegExp('tagName', 'g'), this.name)
    result = result.replace(new RegExp('namePostfix', 'g'), this.namePostfix)
    result = result.replace(new RegExp('tagId', 'g'), this.id)
    result = result.replace(new RegExp('segmentId', 'g'), this.segmentId)
    result = result.replace(new RegExp('typeClass', 'g'), this.typeClass)
    return result
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
  return tag.substring(4, spacePosition)
}
export class SelfClosingTag extends Tag {
  constructor (tagString, segmentId) {
    super(getTagIdFromXliff(tagString), getTagNameFromXliff(tagString), segmentId)
    this.namePostfix = 'sc'
    this.typeClass = 'self-closing'
  }
  toXliff () {
    return '&lt;' + this.name + ' id="' + this.id + '"/&gt;'
  }
}
export function validateString (text) {
  if (typeof (text) === 'undefined' || text === null) {
    throw new ValueMissing()
  }
  if (text === '') {
    throw new ValueEmpty()
  }
}
export class DualOpenTag extends Tag {
  constructor (tagString, segmentId) {
    super(getTagIdFromXliff(tagString), getTagNameFromXliff(tagString), segmentId)
    this.namePostfix = 'do'
    this.typeClass = 'dual-open'
  }
  toXliff () {
    return '&lt;' + this.name + ' id="' + this.id + '"&gt;'
  }
}
export class DualCloseTag extends Tag {
  constructor (openTag) {
    super(openTag.id, openTag.name, openTag.segmentId)
    this.namePostfix = 'dc'
    this.typeClass = 'dual-close'
  }
  toXliff () {
    return '&lt;"/' + this.name + '&gt;'
  }
}
