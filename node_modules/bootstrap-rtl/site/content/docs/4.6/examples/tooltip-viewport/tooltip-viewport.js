$(function () {
  'use strict'

  $('.tooltip-left').tooltip({
    placement: 'left',
    offset: 2,
    boundary: 'viewport'
  })
  $('.tooltip-bottom').tooltip({
    placement: 'bottom',
    offset: 2,
    boundary: 'viewport'
  })

  var containerViewPort = document.getElementsByClassName('container-viewport')[0]

  $('.tooltip-viewport-left').tooltip({
    placement: 'left',
    offset: 2,
    boundary: containerViewPort
  })
  $('.tooltip-viewport-bottom').tooltip({
    placement: 'bottom',
    offset: 2,
    boundary: containerViewPort
  })
})
