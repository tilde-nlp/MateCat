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
  it('builds correct html tag', () => {
    const expectedResult = '<span class="tag self-closing" data-tag-name="gx-sc" data-xlif-id="10" data-class-id="tag-10-1" onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)">gx</span>'
    const selfClosingTag = new SelfClosingTag('&lt;gx id="10"/&gt;', 1)
    expect(selfClosingTag.toHtml()).toBe(expectedResult)
  })
  it('builds correct html tag', () => {
    const expectedResult = '<span class="tag dual-open" data-tag-name="gx-do" data-xlif-id="10" data-class-id="tag-10-1" onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)">gx</span>'
    const dualOpenTag = new DualOpenTag('&lt;gx id="10"&gt;', 1)
    expect(dualOpenTag.toHtml()).toBe(expectedResult)
  })
  it('builds correct html tag', () => {
    const expectedResult = '<span class="tag dual-close" data-tag-name="gx-dc" data-xlif-id="10" data-class-id="tag-10-1" onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)">gx</span>'
    const openTag = new DualOpenTag('&lt;gx id="10"&gt;', 1)
    const dualCloseTag = new DualCloseTag(openTag)
    expect(dualCloseTag.toHtml()).toBe(expectedResult)
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

  beforeEach(() => {
    selfClosingTag = new SelfClosingTag('&lt;gx id="10"/&gt;', 1)
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
  it('throws  exception if duplicate tags found in source', () => {
    const source = 'Some random text' + selfClosingTag.toXliff() + ' another random text' + selfClosingTag.toXliff()
    const testFunction = () => {
      selfClosingTag.replaceToHtml(source)
    }
    expect(testFunction).toThrow(DuplicateTagInSource)
  })
})
