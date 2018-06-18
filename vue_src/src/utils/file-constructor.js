export const FileConstructor = {
  get: function (data) {
    const newFile = {
      id: -1,
      password: -1,
      jobId: -1,
      jobPassword: -1,
      name: -1,
      wordCount: -1,
      segmentCount: -1,
      owner: -1,
      progress: -1,
      created: -1,
      translatedUrl: -1,
      tmpFileId: -1,
      statsLink: -1,
      statusLink: -1,
      isEmpty: false
    }
    for (const key in data) {
      if (newFile.hasOwnProperty(key)) {
        newFile[key] = data[key]
      }
    }
    return newFile
  }
}
