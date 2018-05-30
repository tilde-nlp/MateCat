import _ from 'lodash'
export const FormGenerator = {
  generateForm: function (data) {
    // eslint-disable-next-line no-undef
    let formData = new FormData()
    _.forOwn(data, (value, key) => {
      formData.append(key, value)
    })
    return formData
  }
}
