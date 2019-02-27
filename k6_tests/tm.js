import { get, post, checkOk, msleep} from './utils.js'

const testTm = () => {
    const result = get('tm')
    checkOk(result)
    msleep(1)
}

const testMatches = () => {
    const result = post('tm/matches', {
        projectId: 240,
        projectPassword: 'fd4be30341fe',
        text: 'This block is blue.',
        sourceLang: 'en-US',
        targetLang: 'lv-LV',
        count: 5
    })
    checkOk(result)
    msleep(1)
}

const testConcordance = () => {
    const result = post('tm/concordance-search', {
        projectId: 240,
        text: 'This block is blue.',
        sourceLang: 'en-US',
        targetLang: 'lv-LV'
    })
    checkOk(result);
    msleep(1);
}

export default () => {
    testTm()
    testMatches()
    testConcordance()
}
