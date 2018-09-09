import {
  SelfClosingTag,
  DualOpenTag,
  DualCloseTag
} from '@/utils/segment/segment-tag'
import {
  xliffToHtml,
  replaceAllXliffSelfClosedTags,
  replaceAllXliffDualTags
} from '@/utils/segment/segment-text'
import {ValueMissing} from '@/utils/segment/exceptions'

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

describe('SegmentText.xliffToHtml', () => {
  it('throws exception on missing value', () => {
    expect(xliffToHtml).toThrow(ValueMissing)
  })
  it('returns same text if no tags inside', () => {
    expect(xliffToHtml('', selfClosingTag.segmentId)).toBe('')
  })
  it('converts one self closed tag', () => {
    const original = selfClosingTag.toXliff()
    expect(xliffToHtml(original, selfClosingTag.segmentId)).toBe(selfClosingTag.toHtml())
  })
  it('converts one dual tag', () => {
    const original = dualOpenTag.toXliff() + dualCloseTag.toXliff()
    const expected = dualOpenTag.toHtml() + dualCloseTag.toHtml()
    expect(xliffToHtml(original, dualOpenTag.segmentId)).toBe(expected)
  })
  it('converts two nested dual tags', () => {
    const secondDualOpenTag = new DualOpenTag()
    secondDualOpenTag.id = 2
    secondDualOpenTag.name = dualOpenTag.name
    secondDualOpenTag.segmentId = dualOpenTag.segmentId
    const secondDualCloseTag = new DualCloseTag()
    secondDualCloseTag.id = 2
    secondDualCloseTag.name = dualOpenTag.name
    secondDualCloseTag.segmentId = dualOpenTag.segmentId
    const original = dualOpenTag.toXliff() +
      secondDualOpenTag.toXliff() +
      secondDualCloseTag.toXliff() +
      dualCloseTag.toXliff()
    const expected = dualOpenTag.toHtml() +
      secondDualOpenTag.toHtml() +
      secondDualCloseTag.toHtml() +
      dualCloseTag.toHtml()
    expect(xliffToHtml(original, dualOpenTag.segmentId)).toBe(expected)
  })
  it('converts nested mixed tags', () => {
    const original = dualOpenTag.toXliff() + selfClosingTag.toXliff() + dualCloseTag.toXliff()
    const expected = dualOpenTag.toHtml() + selfClosingTag.toHtml() + dualCloseTag.toHtml()
    expect(xliffToHtml(original, dualOpenTag.segmentId)).toBe(expected)
  })
})

describe('SegmentText.replaceAllXliffSelfClosedTags', () => {
  it('returns same text when no matches found', () => {
    const source = 'Some text with no tags'
    expect(replaceAllXliffSelfClosedTags(source)).toBe(source)
    expect(replaceAllXliffSelfClosedTags('')).toBe('')
  })
})

describe('SegmentText.replaceAllXliffDualTags', () => {
  it('returns same text when no matches found', () => {
    const source = 'Some text with no tags'
    expect(replaceAllXliffDualTags(source)).toBe(source)
    expect(replaceAllXliffDualTags('')).toBe('')
  })
})
