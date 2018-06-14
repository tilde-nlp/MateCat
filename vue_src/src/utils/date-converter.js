export const DateConverter = {
  timeStampToDate: function (timestamp) {
    const date = new Date(timestamp * 1000)
    return ('0' + date.getDate()).slice(-2) + '.' + ('0' + date.getMonth()).slice(-2) + '.' + date.getFullYear()
  },
  nowDate: function () {
    const date = new Date()
    return ('0' + date.getDate()).slice(-2) + '.' + ('0' + date.getMonth()).slice(-2) + '.' + date.getFullYear()
  }
}
