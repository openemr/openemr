/**
 * --------------------------------------------------------------------------
 * Bootstrap (v4.6.1): rtl.js
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/main/LICENSE)
 * Document Direction Detector
 * Used in Carousel.js to correct touch experience
 * --------------------------------------------------------------------------
 */

let documentDirectionIsRtl = false
const htmlElement = document.getElementsByTagName('html')[0]
const htmlElementStyleDir = window.getComputedStyle(htmlElement).direction || ''
const htmlElementAttributeDir = htmlElement.getAttribute('dir') || ''
const documentDirection = htmlElementStyleDir || htmlElementAttributeDir

if (documentDirection && documentDirection.trim().toLowerCase() === 'rtl') {
  documentDirectionIsRtl = true
}

export default {
  documentDirectionIsRtl
}
