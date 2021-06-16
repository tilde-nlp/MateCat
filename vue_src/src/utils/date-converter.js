export const DateConverter = {
  timeStampToDate: function (timestamp) {
    const date = new Date(timestamp * 1000)
    return ('0' + date.getDate()).slice(-2) + '.' + ('0' + (parseInt(date.getMonth()) + 1)).slice(-2) + '.' + date.getFullYear()
  },
  timeStampToFullTime: function (timestamp) {
    const date = new Date(timestamp * 1000)
    return ('0' + date.getHours()).slice(-2) + ':' +
      ('0' + date.getMinutes()).slice(-2) + ':' +
      ('0' + date.getSeconds()).slice(-2) + ' ' +
      ('0' + date.getDate()).slice(-2) + '.' +
      ('0' + (parseInt(date.getMonth()) + 1)).slice(-2) + '.' +
      date.getFullYear()
  },
  nowDate: function () {
    const date = new Date()
    return ('0' + date.getDate()).slice(-2) + '.' + ('0' + (parseInt(date.getMonth()) + 1)).slice(-2) + '.' + date.getFullYear()
  }
}
