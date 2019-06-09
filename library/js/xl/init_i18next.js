
var translationObj = JSON.parse(sessionStorage.getItem('i18n'));

i18n = i18next.init({
    lng: 'selected',
    debug: true,
    resources: {
        selected: {
            translation: translationObj
        }
    }
});
