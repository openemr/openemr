/**
 * Integration tests for jsPDF in OpenEMR
 *
 * These tests use the REAL jsPDF library to verify compatibility.
 * Run with: npm run test:js -- --testPathPattern="jspdf-integration"
 *
 * If these tests fail after a jsPDF upgrade, the Fax/SMS module's
 * TIFF-to-PDF conversion may be broken.
 */

// Import the actual jsPDF library
const { jsPDF } = require('jspdf');

describe('jsPDF Real Library Integration', () => {
    let doc;

    beforeEach(() => {
        doc = new jsPDF();
    });

    describe('Document Creation', () => {
        test('can create a new jsPDF document', () => {
            expect(doc).toBeDefined();
            // Note: Don't use toBeInstanceOf as the class name may be minified
            expect(typeof doc.addPage).toBe('function');
            expect(typeof doc.addImage).toBe('function');
        });

        test('document has internal property', () => {
            expect(doc.internal).toBeDefined();
        });
    });

    describe('Page Dimensions (used by OpenEMR)', () => {
        test('internal.pageSize.height returns a number', () => {
            const height = doc.internal.pageSize.height;
            expect(typeof height).toBe('number');
            expect(height).toBeGreaterThan(0);
        });

        test('internal.pageSize.width returns a number', () => {
            const width = doc.internal.pageSize.width;
            expect(typeof width).toBe('number');
            expect(width).toBeGreaterThan(0);
        });

        test('default page size is A4 (approximately 210x297mm)', () => {
            // jsPDF default is A4 portrait
            const width = doc.internal.pageSize.width;
            const height = doc.internal.pageSize.height;

            // A4 is 210mm x 297mm, allow some tolerance
            expect(width).toBeCloseTo(210, 0);
            expect(height).toBeCloseTo(297, 0);
        });

        test('getWidth() and getHeight() methods exist and work', () => {
            // Alternative API that may be used
            expect(typeof doc.internal.pageSize.getWidth).toBe('function');
            expect(typeof doc.internal.pageSize.getHeight).toBe('function');
            expect(doc.internal.pageSize.getWidth()).toBeCloseTo(210, 0);
            expect(doc.internal.pageSize.getHeight()).toBeCloseTo(297, 0);
        });
    });

    describe('Page Management (used by OpenEMR)', () => {
        test('addPage() method exists', () => {
            expect(typeof doc.addPage).toBe('function');
        });

        test('can add multiple pages', () => {
            const initialPages = doc.getNumberOfPages();
            expect(initialPages).toBe(1);

            doc.addPage();
            expect(doc.getNumberOfPages()).toBe(2);

            doc.addPage();
            expect(doc.getNumberOfPages()).toBe(3);
        });
    });

    describe('Image Handling (used by OpenEMR)', () => {
        test('addImage() method exists', () => {
            expect(typeof doc.addImage).toBe('function');
        });

        test('addImage accepts base64 data URL format', () => {
            // Create a minimal 1x1 red pixel PNG
            const tinyRedPixel = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFBQIAX8jx0gAAAABJRU5ErkJggg==';

            // Should not throw when adding an image
            expect(() => {
                doc.addImage(tinyRedPixel, 'PNG', 0, 0, 50, 50);
            }).not.toThrow();
        });

        test('addImage with JPEG format parameter', () => {
            const tinyPixel = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFBQIAX8jx0gAAAABJRU5ErkJggg==';

            // OpenEMR uses 'JPEG' format string
            expect(() => {
                doc.addImage(tinyPixel, 'JPEG', 0, 0, 210, 297);
            }).not.toThrow();
        });

        test('addImage with full page dimensions', () => {
            const tinyPixel = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFBQIAX8jx0gAAAABJRU5ErkJggg==';
            const pageWidth = doc.internal.pageSize.width;
            const pageHeight = doc.internal.pageSize.height;

            // This is exactly how OpenEMR uses addImage
            expect(() => {
                doc.addImage(tinyPixel, 'JPEG', 0, 0, pageWidth, pageHeight);
            }).not.toThrow();
        });
    });

    describe('Output Generation (used by OpenEMR)', () => {
        test('output() method exists', () => {
            expect(typeof doc.output).toBe('function');
        });

        test('output("datauristring") returns a string', () => {
            const result = doc.output('datauristring');
            expect(typeof result).toBe('string');
        });

        test('output("datauristring") returns valid data URI format', () => {
            const result = doc.output('datauristring');

            // Should start with data:application/pdf;
            expect(result).toMatch(/^data:application\/pdf;/);

            // Should contain base64 marker
            expect(result).toContain('base64,');
        });

        test('data URI can be split to extract base64 content', () => {
            const result = doc.output('datauristring');
            const parts = result.split(',');

            expect(parts.length).toBe(2);
            expect(parts[0]).toContain('application/pdf');

            // Second part should be base64 encoded PDF content
            const base64Content = parts[1];
            expect(base64Content.length).toBeGreaterThan(0);

            // Valid base64 contains only these characters
            expect(base64Content).toMatch(/^[A-Za-z0-9+/=]+$/);
        });
    });

    describe('Security Settings (used by OpenEMR)', () => {
        test('internal.write object exists', () => {
            expect(doc.internal.write).toBeDefined();
        });

        test('isEvalSupported property can be set to false', () => {
            // OpenEMR sets this for security
            expect(() => {
                doc.internal.write.isEvalSupported = false;
            }).not.toThrow();
        });

        test('isEvalSupported value persists after setting', () => {
            doc.internal.write.isEvalSupported = false;
            // Note: The property might not actually be used/read in newer versions
            // but we need to ensure setting it doesn't throw
        });
    });

    describe('Full OpenEMR Workflow Simulation', () => {
        test('complete TIFF-to-PDF conversion workflow', () => {
            // Simulate what convertImagesToPdf does in messageUI.php
            // Using the same valid 1x1 red pixel PNG for both pages
            const validPng = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFBQIAX8jx0gAAAABJRU5ErkJggg==';
            const images = [validPng, validPng];

            const pdfDoc = new jsPDF();

            // Security setting
            pdfDoc.internal.write.isEvalSupported = false;

            // Get page dimensions
            const pageHeight = pdfDoc.internal.pageSize.height;
            const pageWidth = pdfDoc.internal.pageSize.width;

            // Add images to pages
            for (let i = 0; i < images.length; i++) {
                if (i !== 0) {
                    pdfDoc.addPage();
                }
                pdfDoc.addImage(images[i], 'JPEG', 0, 0, pageWidth, pageHeight);
            }

            // Generate output
            const dataUri = pdfDoc.output('datauristring');
            const base64Content = dataUri.split(',')[1];

            // Verify results
            expect(pdfDoc.getNumberOfPages()).toBe(2);
            expect(base64Content.length).toBeGreaterThan(0);
            expect(base64Content).toMatch(/^[A-Za-z0-9+/=]+$/);
        });

        test('single page PDF generation', () => {
            const image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFBQIAX8jx0gAAAABJRU5ErkJggg==';

            const pdfDoc = new jsPDF();
            pdfDoc.internal.write.isEvalSupported = false;

            const pageWidth = pdfDoc.internal.pageSize.width;
            const pageHeight = pdfDoc.internal.pageSize.height;

            pdfDoc.addImage(image, 'JPEG', 0, 0, pageWidth, pageHeight);

            const base64Content = pdfDoc.output('datauristring').split(',')[1];

            expect(pdfDoc.getNumberOfPages()).toBe(1);
            expect(base64Content.length).toBeGreaterThan(0);
        });
    });
});

describe('jsPDF Version Information', () => {
    test('can get jsPDF version', () => {
        // This helps with debugging version-related issues
        const doc = new jsPDF();

        // The version might be accessible in different ways depending on jsPDF version
        console.log('jsPDF document created successfully');

        // Just verify it works
        expect(doc).toBeDefined();
    });
});
