<?php
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // These were adapted from library/classes/Prescription.class.php:

 $form_array = array('', xl('suspension'), xl('tablet'), xl('capsule'), xl('solution'), xl('tsp'),
  xl('ml'), xl('units'), xl('inhalations'), xl('gtts(drops)'));

 $unit_array = array('', 'mg', 'mg/1cc', 'mg/2cc', 'mg/3cc', 'mg/4cc',
  'mg/5cc', 'grams', 'mcg');

 $route_array = array('', xl('Per Oris'), xl('Per Rectum'), xl('To Skin'),
  xl('To Affected Area'), xl('Sublingual'), xl('OS'), xl('OD'), xl('OU'), xl('SQ'), xl('IM'), xl('IV'),
  xl('Per Nostril'));

 $interval_array = array('', 'b.i.d.', 't.i.d.', 'q.i.d.', 'q.3h', 'q.4h',
  'q.5h', 'q.6h', 'q.8h', 'q.d.');

 $interval_array_verbose = array('',
  xl('twice daily'),
  xl('3 times daily'),
  xl('4 times daily'),
  xl('every 3 hours'),
  xl('every 4 hours'),
  xl('every 5 hours'),
  xl('every 6 hours'),
  xl('every 8 hours'),
  xl('daily'));

 $route_array_verbose = array('',
  xl('by mouth'),
  xl('rectally'),
  xl('to skin'),
  xl('to affected area'),
  xl('under tongue'),
  xl('in left eye'),
  xl('in right eye'),
  xl('in each eye'),
  xl('subcutaneously'),
  xl('intramuscularly'),
  xl('intravenously'),
  xl('in nostril'));

 $substitute_array = array('', xl('Allowed'), xl('Not Allowed'));
?>
