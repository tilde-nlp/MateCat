import {
  replaceAllSelfClosedTags,
  replaceOneSelfClosedTag
} from '@/utils/segment/segment-text'
import {
  SelfClosingTag
} from '@/utils/segment/segment-tag'
import {
  ValueMissing,
  DuplicateTagInSource
} from '@/utils/segment/exceptions'

describe('ToHtmlConverter.replaceOneSelfClosedTag', () => {
  it('replaces xlif tag with html tag', () => {
    const tagToReplace = '&lt;gx id="10"/&gt;'
    const html = 'Doge 420 iet pa ceļu' + tagToReplace + ' un nu jā tipa...'
    const selfClosedTag = new SelfClosingTag(tagToReplace, 1)
    const expectedResult = 'Doge 420 iet pa ceļu' + selfClosedTag.toHtml() + ' un nu jā tipa...'
    expect(replaceOneSelfClosedTag(tagToReplace, html, 1)).toBe(expectedResult)
  })
  it('if text with no tag given', () => {
    const tagToReplace = '&lt;gx id="10"/&gt;'
    const html = 'Doge 420 iet pa ceļu un nu jā tipa...'
    expect(replaceOneSelfClosedTag(tagToReplace, html, 1)).toBe(html)
  })
  it('throws  exception if duplicate tags found in source', () => {
    const tagToReplace = '&lt;gx id="10"/&gt;'
    const html = 'Doge 420 iet pa ceļu' + tagToReplace + ' un nu jā tipa...' + tagToReplace
    const testFunction = () => {
      replaceOneSelfClosedTag(tagToReplace, html, 1)
    }
    expect(testFunction).toThrow(DuplicateTagInSource)
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
