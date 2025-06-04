const headReplace = require('./head-replace').headReplace;

describe('headReplace', () => {
    const originalHead = '<head>This is the original head</head>';
    const body = '<ClinicalDocument>This is the document body</ClinicalDocument>';
    const input = originalHead + body;
    
    describe('empty xslUrl', () => {
        it('should replace the original head with the default CDA head', () => {
            const defaultCDAHead = `<?xml version="1.0" encoding="UTF-8"?>\n<?xml-stylesheet type="text/xsl" href="CDA.xsl"?>\n`;
            expect(headReplace(input)).toEqual(defaultCDAHead + body);
            expect(headReplace(input, '     ')).toEqual(defaultCDAHead + body);
        });
    });
    
    describe('custom xslUrl', () => {
        it('should replace the original head with the custom CDA head, inserting the provided xlsUrl', () => {
            const customCDAHead = `<?xml version="1.0" encoding="UTF-8"?>\n<?xml-stylesheet type="text/xsl" href="openEMR.com"?>\n`;
            expect(headReplace(input, 'openEMR.com')).toEqual(customCDAHead + body);
        });
    });
});
