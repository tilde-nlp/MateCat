import {
  SelfClosingTag,
  DualOpenTag,
  DualCloseTag,
  getTagIdFromXliff,
  getTagNameFromXliff
} from '@/utils/segment/segment-tag'
import {
  ValueMissing,
  ValueEmpty,
  DuplicateTagInSource
} from '@/utils/segment/exceptions'

describe('SegmentTag.toHtml', () => {
  it('builds correct self closing html tag', () => {
    const expectedResult = '<span class="tag self-closing" data-tag-name="gx-sc" data-xlif-id="10" data-class-id="tag-10-1" onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)">gx</span>'
    const tag = new SelfClosingTag()
    tag.id = 10
    tag.name = 'gx'
    tag.segmentId = 1
    expect(tag.toHtml()).toBe(expectedResult)
  })
  it('builds correct dual open html tag', () => {
    const expectedResult = '<span class="tag dual-open" data-tag-name="gx-do" data-xlif-id="10" data-class-id="tag-10-1" onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)">gx</span>'
    const tag = new DualOpenTag()
    tag.id = 10
    tag.name = 'gx'
    tag.segmentId = 1
    expect(tag.toHtml()).toBe(expectedResult)
  })
  it('builds correct dual close html tag', () => {
    const expectedResult = '<span class="tag dual-close" data-tag-name="gx-dc" data-xlif-id="10" data-class-id="tag-10-1" onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)">gx</span>'
    const tag = new DualCloseTag()
    tag.id = 10
    tag.name = 'gx'
    tag.segmentId = 1
    expect(tag.toHtml()).toBe(expectedResult)
  })
})

describe('SegmentTag.fromXliff', () => {
  it('builds correct self closing xliff tag', () => {
    const tag = new SelfClosingTag()
    tag.fromXliff('&lt;gx id="10"/&gt;')
    tag.segmentId = 1
    expect(tag.id).toBe('10')
    expect(tag.name).toBe('gx')
    expect(tag.segmentId).toBe(1)
    expect(tag.namePostfix).toBe('sc')
    expect(tag.typeClass).toBe('self-closing')
  })
  it('builds correct dual open xliff tag', () => {
    const tag = new DualOpenTag()
    tag.fromXliff('&lt;gx id="10"&gt;')
    tag.segmentId = 1
    expect(tag.id).toBe('10')
    expect(tag.name).toBe('gx')
    expect(tag.segmentId).toBe(1)
    expect(tag.namePostfix).toBe('do')
    expect(tag.typeClass).toBe('dual-open')
  })
  it('builds correct dual close xliff tag', () => {
    const tag = new DualCloseTag()
    tag.fromXliff('&lt;/gx&gt;')
    tag.segmentId = 1
    expect(tag.id).toBe('')
    expect(tag.name).toBe('gx')
    expect(tag.segmentId).toBe(1)
    expect(tag.namePostfix).toBe('dc')
    expect(tag.typeClass).toBe('dual-close')
  })
})

describe('SegmentTag.toXliff', () => {
  it('builds correct self closing xliff tag', () => {
    const expectedResult = '&lt;gx id="10"/&gt;'
    const tag = new SelfClosingTag()
    tag.id = 10
    tag.name = 'gx'
    tag.segmentId = 1
    expect(tag.toXliff()).toBe(expectedResult)
  })
  it('builds correct dual open xliff tag', () => {
    const expectedResult = '&lt;gx id="10"&gt;'
    const tag = new DualOpenTag()
    tag.id = 10
    tag.name = 'gx'
    tag.segmentId = 1
    expect(tag.toXliff()).toBe(expectedResult)
  })
  it('builds correct dual close xliff tag', () => {
    const expectedResult = '&lt;/gx&gt;'
    const tag = new DualCloseTag()
    tag.id = 10
    tag.name = 'gx'
    tag.segmentId = 1
    expect(tag.toXliff()).toBe(expectedResult)
  })
})

describe('SegmentTag.getTagIdFromXliff', () => {
  it('throws exception on missing value', () => {
    expect(getTagIdFromXliff).toThrow(ValueMissing)
  })
  it('throws exception on empty string', () => {
    const testFunction = () => {
      getTagIdFromXliff('')
    }
    expect(testFunction).toThrow(ValueEmpty)
  })
  it('gets correct id', () => {
    const tag = '&lt;bx id="_10"&gt;'
    expect(getTagIdFromXliff(tag)).toBe('_10')
  })
})

describe('SegmentTag.getTagNameFromXliff', () => {
  it('throws exception on missing value', () => {
    expect(getTagNameFromXliff).toThrow(ValueMissing)
  })
  it('throws exception on empty string', () => {
    const testFunction = () => {
      getTagNameFromXliff('')
    }
    expect(testFunction).toThrow(ValueEmpty)
  })
  it('gets correct id', () => {
    const tag = '&lt;bx id="_10"&gt;'
    expect(getTagNameFromXliff(tag)).toBe('bx')
  })
})

describe('SegmentTag-SelfClosing.replaceToHtml', () => {
  let selfClosingTag
  let dualOpenTag
  let dualCloseTag

  beforeEach(() => {
    selfClosingTag = new SelfClosingTag()
    selfClosingTag.id = 10
    selfClosingTag.name = 'gx'
    selfClosingTag.segmentId = 1
    dualOpenTag = new DualOpenTag()
    dualOpenTag.id = 10
    dualOpenTag.name = 'gx'
    dualOpenTag.segmentId = 1
    dualCloseTag = new DualCloseTag()
    dualCloseTag.id = 10
    dualCloseTag.name = 'gx'
    dualCloseTag.segmentId = 1
  })

  it('replaces xliff tag with html tag', () => {
    const source = 'Random text' + selfClosingTag.toXliff() + ' another random text'
    const expectedResult = 'Random text' + selfClosingTag.toHtml() + ' another random text'
    expect(selfClosingTag.replaceToHtml(source)).toBe(expectedResult)
  })
  it('if text with no tag given', () => {
    const source = 'Some random text without tags'
    expect(selfClosingTag.replaceToHtml(source)).toBe(source)
  })
  it('throws exception if duplicate self closing tags found in source', () => {
    const source = 'Some random text' + selfClosingTag.toXliff() + ' another random text' + selfClosingTag.toXliff()
    const testFunction = () => {
      selfClosingTag.replaceToHtml(source)
    }
    expect(testFunction).toThrow(DuplicateTagInSource)
  })
  it('throws exception if duplicate dual open tags found in source', () => {
    const source = 'Some random text' + dualOpenTag.toXliff() + ' another random text' + dualOpenTag.toXliff()
    const testFunction = () => {
      dualOpenTag.replaceToHtml(source)
    }
    expect(testFunction).toThrow(DuplicateTagInSource)
  })
})
