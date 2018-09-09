import {SelfClosingTag} from '@/utils/segment/segment-tag'
import {
  xliffToHtml,
  replaceAllXliffSelfClosedTags
} from '@/utils/segment/segment-text'
import {ValueMissing} from '@/utils/segment/exceptions'

let selfClosingTag
let segmentId

beforeEach(() => {
  segmentId = 3
  selfClosingTag = new SelfClosingTag('&lt;bx id="5"/&gt;', 3)
})

describe('SegmentText.xliffToHtml', () => {
  it('throws exception on missing value', () => {
    expect(xliffToHtml).toThrow(ValueMissing)
  })
  it('returns same text if no tags inside', () => {
    expect(xliffToHtml('', segmentId)).toBe('')
  })
  it('converts one self closed tag', () => {
    const original = selfClosingTag.toXliff()
    expect(xliffToHtml(original, segmentId)).toBe(selfClosingTag.toHtml())
  })
})

describe('SegmentText.replaceAllXliffSelfClosedTags', () => {
  it('returns same text when no matches found', () => {
    const source = 'Some text with no tags'
    expect(replaceAllXliffSelfClosedTags(source)).toBe(source)
    expect(replaceAllXliffSelfClosedTags('')).toBe('')
  })
})
