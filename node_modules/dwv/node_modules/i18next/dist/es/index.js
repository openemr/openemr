import i18next from './i18next.js';

export default i18next;

export var changeLanguage = i18next.changeLanguage.bind(i18next);
export var cloneInstance = i18next.cloneInstance.bind(i18next);
export var createInstance = i18next.createInstance.bind(i18next);
export var dir = i18next.dir.bind(i18next);
export var exists = i18next.exists.bind(i18next);
export var getFixedT = i18next.getFixedT.bind(i18next);
export var init = i18next.init.bind(i18next);
export var loadLanguages = i18next.loadLanguages.bind(i18next);
export var loadNamespaces = i18next.loadNamespaces.bind(i18next);
export var loadResources = i18next.loadResources.bind(i18next);
export var off = i18next.off.bind(i18next);
export var on = i18next.on.bind(i18next);
export var setDefaultNamespace = i18next.setDefaultNamespace.bind(i18next);
export var t = i18next.t.bind(i18next);
export var use = i18next.use.bind(i18next);