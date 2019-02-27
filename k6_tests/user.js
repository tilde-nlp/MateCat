import { get, checkOk, msleep } from './utils.js'

export default () => {
    const result = get('profile')
    checkOk(result)
    msleep(1)
  }
