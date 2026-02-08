import globals from "globals";
import jest from "eslint-plugin-jest";
import path from "node:path";
import { fileURLToPath } from "node:url";
import js from "@eslint/js";
import { FlatCompat } from "@eslint/eslintrc";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const compat = new FlatCompat({
    baseDirectory: __dirname,
    recommendedConfig: js.configs.recommended,
    allConfig: js.configs.all
});

export default [{
    ignores: [
        "**/node_modules",
        "interface/forms/eye_mag/js",
        "interface/forms/questionnaire_assessments/lforms",
        "interface/main/calendar/modules/PostCalendar/pnincludes/*.js",
        "interface/modules/custom_modules/oe-module-comlink-telehealth/public/assets/js/dist",
        "interface/modules/custom_modules/oe-module-comlink-telehealth/public/assets/js/src/cvb.min.js",
        "interface/modules/zend_modules/public/js/lib/**/*.js",
        "interface/super/rules/www/js/cdr-multiselect",
        "interface/super/rules/www/js/jQuery.autocomplete.js",
        "interface/super/rules/www/js/jQuery.fn.sortElements.js",
        "library/ESign/js/jquery.esign.js",
        "library/js/vendors/validate/validate_modified.js",
        "library/js/xl/**/*.js",
        "library/js/u2f-api.js",
        "library/js/SearchHighlight.js",
        "library/js/DocumentTreeMenu.js",
        "library/js/CategoryTreeMenu.js",
        "portal/sign/assets/signature_pad.umd.js",
        "portal/patient/scripts",
        "public/assets",
        "**/swagger",
        "**/vendor",
        "Documentation/EHI_Export/**/*.js",
    ],
}, ...compat.extends("eslint:recommended"), {
    languageOptions: {
        globals: {
            ...globals.browser,
            ...globals.commonjs,
            ...globals.jquery,
            ...jest.environments.globals.globals,
            Atomics: "readonly",
            SharedArrayBuffer: "readonly",
        },

        ecmaVersion: "latest",
        sourceType: "module",
    },

    rules: {
        "no-undef": "warn",
        "no-unused-vars": "warn",
        "no-redeclare": "warn",
    },
}, ...compat.extends("plugin:jest/recommended").map(config => ({
    ...config,
    files: ["**/*.spec.js"],
})), {
    files: ["**/*.spec.js"],

    plugins: {
        jest,
    },
}];
