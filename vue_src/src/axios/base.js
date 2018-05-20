import axios from 'axios'
import {CONFIG} from '../config'

export const HTTP = axios.create({
  baseURL: CONFIG.apiUrl,
  headers: {
    // Authorization: 'Bearer {token}'
  }
})
