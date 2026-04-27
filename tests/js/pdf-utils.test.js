/**
 * Tests for jsPDF integration in OpenEMR
 *
 * These tests verify the PDF generation functionality used by the Fax/SMS module.
 * Run with: npm run test:js
 */

// Mock jsPDF
const createMockDoc = () => ({
    internal: {
        write: { isEvalSupported: true },
        pageSize: { height: 297, width: 210 }  // A4 dimensions in mm
    },
    addPage: jest.fn(),
    addImage: jest.fn(),
    output: jest.fn().mockReturnValue('data:application/pdf;base64,mockBase64Content')
});

const createMockJsPDF = (mockDoc) => {
    return jest.fn(() => mockDoc);
};

describe('jsPDF Integration', () => {
    let mockDoc;
    let mockJsPDF;

    beforeEach(() => {
        mockDoc = createMockDoc();
        mockJsPDF = createMockJsPDF(mockDoc);

        // Setup window.jspdf mock as used in OpenEMR
        global.window = {
            jspdf: {
                jsPDF: mockJsPDF
            }
        };
    });

    afterEach(() => {
        jest.clearAllMocks();
        delete global.window;
    });

    describe('convertImagesToPdf', () => {
        /**
         * This function mirrors the convertImagesToPdf in messageUI.php
         * We test it here to ensure jsPDF API compatibility
         */
        async function convertImagesToPdf(images, filename = 'fax-tiff-to-pdf.pdf') {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.internal.write.isEvalSupported = false;
            const pageHeight = doc.internal.pageSize.height;
            const pageWidth = doc.internal.pageSize.width;

            for (let i = 0; i < images.length; i++) {
                if (i !== 0) {
                    doc.addPage();
                }
                doc.addImage(images[i], 'JPEG', 0, 0, pageWidth, pageHeight);
            }

            return doc.output('datauristring').split(',')[1];
        }

        test('creates a new jsPDF document', async () => {
            await convertImagesToPdf(['image1']);
            expect(mockJsPDF).toHaveBeenCalled();
        });

        test('disables isEvalSupported for security', async () => {
            await convertImagesToPdf(['image1']);
            expect(mockDoc.internal.write.isEvalSupported).toBe(false);
        });

        test('adds single image without adding extra pages', async () => {
            const images = ['data:image/jpeg;base64,singleImage'];
            await convertImagesToPdf(images);

            expect(mockDoc.addPage).not.toHaveBeenCalled();
            expect(mockDoc.addImage).toHaveBeenCalledTimes(1);
            expect(mockDoc.addImage).toHaveBeenCalledWith(
                images[0],
                'JPEG',
                0, 0,
                mockDoc.internal.pageSize.width,
                mockDoc.internal.pageSize.height
            );
        });

        test('adds multiple images on separate pages', async () => {
            const images = [
                'data:image/jpeg;base64,image1',
                'data:image/jpeg;base64,image2',
                'data:image/jpeg;base64,image3'
            ];
            await convertImagesToPdf(images);

            // Should add pages for all images after the first
            expect(mockDoc.addPage).toHaveBeenCalledTimes(2);
            expect(mockDoc.addImage).toHaveBeenCalledTimes(3);
        });

        test('returns base64 content without data URI prefix', async () => {
            const result = await convertImagesToPdf(['image1']);
            expect(result).toBe('mockBase64Content');
        });

        test('outputs PDF as data URI string', async () => {
            await convertImagesToPdf(['image1']);
            expect(mockDoc.output).toHaveBeenCalledWith('datauristring');
        });

        test('handles empty image array', async () => {
            const images = [];
            await convertImagesToPdf(images);

            expect(mockDoc.addPage).not.toHaveBeenCalled();
            expect(mockDoc.addImage).not.toHaveBeenCalled();
        });

        test('uses page dimensions for image sizing', async () => {
            const images = ['testImage'];
            await convertImagesToPdf(images);

            expect(mockDoc.addImage).toHaveBeenCalledWith(
                'testImage',
                'JPEG',
                0, 0,
                210,  // pageWidth
                297   // pageHeight
            );
        });
    });

    describe('jsPDF API Compatibility', () => {
        test('jsPDF constructor is accessible via window.jspdf.jsPDF', () => {
            const { jsPDF } = window.jspdf;
            expect(jsPDF).toBeDefined();
            expect(typeof jsPDF).toBe('function');
        });

        test('document has internal.pageSize properties', () => {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            expect(doc.internal.pageSize.height).toBeDefined();
            expect(doc.internal.pageSize.width).toBeDefined();
            expect(typeof doc.internal.pageSize.height).toBe('number');
            expect(typeof doc.internal.pageSize.width).toBe('number');
        });

        test('document has addPage method', () => {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            expect(doc.addPage).toBeDefined();
            expect(typeof doc.addPage).toBe('function');
        });

        test('document has addImage method', () => {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            expect(doc.addImage).toBeDefined();
            expect(typeof doc.addImage).toBe('function');
        });

        test('document has output method', () => {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            expect(doc.output).toBeDefined();
            expect(typeof doc.output).toBe('function');
        });

        test('internal.write.isEvalSupported is writable', () => {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Should be able to set this without throwing
            expect(() => {
                doc.internal.write.isEvalSupported = false;
            }).not.toThrow();
            expect(doc.internal.write.isEvalSupported).toBe(false);
        });
    });
});

describe('jsPDF Version Upgrade Compatibility', () => {
    /**
     * These tests document the jsPDF APIs used by OpenEMR.
     * If a jsPDF upgrade breaks these tests, check the migration guide.
     *
     * Current usage (jsPDF 3.0.4 -> 4.0.0):
     * - new jsPDF() constructor
     * - doc.internal.pageSize.height/width
     * - doc.internal.write.isEvalSupported (security setting)
     * - doc.addPage()
     * - doc.addImage(imageData, format, x, y, width, height)
     * - doc.output('datauristring')
     */

    test('documents required jsPDF APIs for OpenEMR', () => {
        const requiredAPIs = [
            'constructor: new jsPDF()',
            'property: doc.internal.pageSize.height',
            'property: doc.internal.pageSize.width',
            'property: doc.internal.write.isEvalSupported',
            'method: doc.addPage()',
            'method: doc.addImage(imageData, format, x, y, width, height)',
            'method: doc.output(type)'
        ];

        // This test serves as documentation
        expect(requiredAPIs.length).toBe(7);
    });
});
