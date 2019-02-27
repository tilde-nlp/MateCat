import { group } from 'k6'
import MTTest from './mt.js'
import UserTest from './user.js'
import TMTest from './tm.js'

export const options = {
    vus: 50,
    duration: '30s'
}

export default function() {
  group('User', UserTest)
  group('MT', MTTest)
  group('TM', TMTest)
}
