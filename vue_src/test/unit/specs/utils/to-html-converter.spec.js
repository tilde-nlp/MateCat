import {
  getId,
  getTagName,
  replaceAllSelfClosedTags,
  buildHtmlTag,
  SelfClosingTag,
  DualOpenTag,
  DualCloseTag
} from '@/utils/to-html-converter'
import {ValueMissing} from '@/utils/exceptions/value-missing'
import {ValueEmpty} from '@/utils/exceptions/value-empty'

describe('ToHtmlConverter.getId', () => {
  it('throws exception on missing value', () => {
    expect(getId).toThrow(ValueMissing)
  })
  it('throws exception on empty string', () => {
    const testFunction = () => {
      getId('')
    }
    expect(testFunction).toThrow(ValueEmpty)
  })
  it('gets correct id', () => {
    const tag = '&lt;bx id="_10"&gt;'
    expect(getId(tag)).toBe('_10')
  })
})

describe('ToHtmlConverter.getTagName', () => {
  it('throws exception on missing value', () => {
    expect(getTagName).toThrow(ValueMissing)
  })
  it('throws exception on empty string', () => {
    const testFunction = () => {
      getTagName('')
    }
    expect(testFunction).toThrow(ValueEmpty)
  })
  it('gets correct id', () => {
    const tag = '&lt;bx id="_10"&gt;'
    expect(getTagName(tag)).toBe('bx')
  })
})

describe('ToHtmlConverter.buildHtmlTag', () => {
  it('builds correct html tag', () => {
    const expectedResult = '<span class="tag self-closing" data-tag-name="gx-sc" data-xlif-id="10" data-class-id="tag-10-1" onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)">gx</span>'
    expect(buildHtmlTag(new SelfClosingTag('&lt;gx id="10"&gt;', 1))).toBe(expectedResult)
  })
  it('builds correct html tag', () => {
    const expectedResult = '<span class="tag dual-open" data-tag-name="gx-do" data-xlif-id="10" data-class-id="tag-10-1" onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)">gx</span>'
    expect(buildHtmlTag(new DualOpenTag('&lt;gx id="10"&gt;', 1))).toBe(expectedResult)
  })
  it('builds correct html tag', () => {
    const expectedResult = '<span class="tag dual-close" data-tag-name="gx-dc" data-xlif-id="10" data-class-id="tag-10-1" onmouseenter="onTagMouseEnter(this)" onmouseleave="onTagMouseLeave(this)">gx</span>'
    const openTag = new DualOpenTag('&lt;gx id="10"&gt;', 1)
    expect(buildHtmlTag(new DualCloseTag(openTag))).toBe(expectedResult)
  })
})

describe('ToHtmlConverter.replaceAllSelfClosedTags', () => {
  it('throws exception on missing value', () => {
    expect(replaceAllSelfClosedTags).toThrow(ValueMissing)
  })
  it('returns same text when no matches found', () => {
    const text = 'Some text with no tags'
    expect(replaceAllSelfClosedTags(text)).toBe(text)
    expect(replaceAllSelfClosedTags('')).toBe('')
  })
})
