import {SelfClosingTag} from '@/utils/segment/segment-tag'
import {xliffToHtml} from '@/utils/segment/segment-text'

let selfClosingTag
let segmentId

beforeEach(() => {
  segmentId = 3
  selfClosingTag = new SelfClosingTag('&lt;bx id="5"/&gt;', 3)
})
describe('SegmentText.xliffToHtml', () => {
  it('returns same text if no tags inside', () => {
    expect(xliffToHtml('', segmentId)).toBe('')
  })
  it('converts one self closed tag', () => {
    const original = selfClosingTag.toXliff()
    expect(xliffToHtml(original, segmentId)).toBe(selfClosingTag.toHtml())
  })
})
