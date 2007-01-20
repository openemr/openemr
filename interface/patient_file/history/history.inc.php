<?php
 // Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This describes information stored in the history_data table.

 // Exams: database name and descriptive name, in order of on-screen
 // presentation, and with the exam results index for last_exam_results:
 //
 if ($GLOBALS['athletic_team']) {
  $exams = array(
   "last_cardiac_echo"              => xl('08 Cardiac Echo'),
   "last_ecg"                       => xl('07 ECG'),
   "last_physical_exam"             => xl('05 Physical Exam'),
   "last_breast_exam"               => xl('00 Breast Exam'),
   "last_mammogram"                 => xl('01 Mammogram')
  );
 } else {
  $exams = array(
   "last_breast_exam"               => xl('00 Breast Exam'),
   "last_cardiac_echo"              => xl('08 Cardiac Echo'),
   "last_ecg"                       => xl('07 ECG'),
   "last_gynocological_exam"        => xl('02 Gynecological Exam'),
   "last_mammogram"                 => xl('01 Mammogram'),
   "last_physical_exam"             => xl('05 Physical Exam'),
   "last_prostate_exam"             => xl('04 Prostate Exam'),
   "last_rectal_exam"               => xl('03 Rectal Exam'),
   "last_sigmoidoscopy_colonoscopy" => xl('06 Sigmoid/Colonoscopy')
  );
 }

 // Deprecated surgery date items that should be shown only if there
 // is data, and which should be moved to the lists table:
 //
 $obsoletes = array(
  'cataract_surgery' => xl('Cataract Surgery'),
  'tonsillectomy'    => xl('Tonsillectomy'),
  'appendectomy'     => xl('Appendectomy'),
  'cholecystestomy'  => xl('Cholecystestomy'),
  'heart_surgery'    => xl('Heart Surgery'),
  'hysterectomy'     => xl('Hysterectomy'),
  'hernia_repair'    => xl('Hernia Repair'),
  'hip_replacement'  => xl('Hip Replacement'),
  'knee_replacement' => xl('Knee Replacement')
 );
?>
