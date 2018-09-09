import {getId} from '@/utils/to-html-converter'

describe('ToHtmlConverter', () => {
  it('Gets correct id', () => {
    const tag = '&lt;g id="1"&gt;'
    expect(getId(tag)).toBe(1)
  })
})
