import axios from 'axios'

export const HTTP = axios.create({
  baseURL: '',
  headers: {
    'Content-Type': 'multipart/form-data'
    // Authorization: 'Bearer {token}'
  }
})
