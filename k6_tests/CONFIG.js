export default {
    virtualUserCount: 10, // Used by k6 testing framework
    testDuration: '2m', // Used by k6 testing framework
    apiUrl: 'http://local.matecat.com/', // Cat api being tested
    serviceUrl: 'http://hugodevlogic.tilde.lv:4000/Usermanager.asmx/', // LetsMT Service api for test user creation
    adminUsername: 'oskars.petriks@tilde.lv', // User in service api with permissions to CreateNewAPIUserID and DeleteApiUser
    adminPassword: 'HugoDev!', // Password for admin user
    clientIdPassword: 'tH15*!5+w3Ry=53cR3t', // LetsMT Service authSecretKey, used for clientId Basic authorization
    mtSystemId: 'smt-31896be2-4f05-428e-a3c1-1221ab78141a'
}