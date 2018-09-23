import axios from 'axios'
import {CONFIG} from '../CONFIG'
import {VanillaCookies} from 'utils/vanilla-cookies'

export const HTTP = axios.create({
  baseURL: '',
  headers: {
    'Content-Type': 'multipart/form-data'
  }
})

HTTP.interceptors.request.use(function (config) {
  const hasClientId = typeof (config.headers['client-id']) !== 'undefined'
  const jwt = VanillaCookies.getCookie('jwt')
  if (!hasClientId && jwt !== '') {
    config.headers['Authorization'] = 'Bearer ' + jwt
  }
  return config
}, function (error) {
  return Promise.reject(error)
})

HTTP.interceptors.response.use(function (response) {
  return response
}, function (error) {
  if (error.response.status === 401) {
    const redirect = CONFIG.authRedirect + encodeURIComponent(window.location.href)
    window.location.replace(redirect)
  }
  return Promise.reject(error)
})
