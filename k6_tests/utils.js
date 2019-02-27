import { check, sleep } from 'k6'
import http from 'k6/http'

const apiUrl = 'http://local.matecat.com/'
export const msleep = (__ENV.KS_SLEEP === 'yes' ? sleep : x => x)

export const checkOk = result => {
    check(result, {
        'status was 200': (r) => r.status == 200
      })
}

export const get = endpoint => {
  return http.get(apiUrl + endpoint)
}

export const post = (endpoint, payload) => {
    return http.post(apiUrl + endpoint, payload)
}
