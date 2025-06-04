'use strict';

function headReplace(content, xslUrl = '') {
    const xsl = (typeof xslUrl === 'string') && (xslUrl.trim() !== '')
        ? xslUrl
        : 'CDA.xsl';

    const body = content.substring(content.search(/<ClinicalDocument/i));
    const head = '<?xml version="1.0" encoding="UTF-8"?>\n'
        + `<?xml-stylesheet type="text/xsl" href="${xsl}"?>\n`;
    return head + body;
}

exports.headReplace = headReplace;
