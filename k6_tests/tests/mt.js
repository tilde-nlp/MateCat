import { post, checkOk, msleep } from '../utils.js'

export default data => {
  const requestData = {
    text: 'This block is blue.',
    mtId: data.mtSystemId
  }
  const result = post('mt/matches', requestData, data.params)
  checkOk(result)
  msleep(1)
}
