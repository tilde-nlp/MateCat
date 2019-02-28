import { get, checkOk, msleep } from '../utils.js'

export default data => {
    const result = get('profile', data.params)
    checkOk(result)
    msleep(1)
  }
