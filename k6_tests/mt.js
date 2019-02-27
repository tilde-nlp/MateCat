import { post, checkOk, msleep } from './utils.js'

const mtSystemId = 'smt-31896be2-4f05-428e-a3c1-1221ab78141a'

export default () => {
    const result = post('mt/matches', {
      text: 'This block is blue.',
      mtId: mtSystemId
    })
    checkOk(result)
    msleep(1)
  }
