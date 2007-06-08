<?php
 // Copyright (C) 2005-2007 Rod Roark <rod@sunsetsystems.com>
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
   "last_cardiac_echo"              => '08 ' . xl('Cardiac Echo'),
   "last_ecg"                       => '07 ' . xl('ECG'),
   "last_physical_exam"             => '05 ' . xl('Physical Exam'),
   "last_breast_exam"               => '00 ' . xl('Breast Exam'),
   "last_mammogram"                 => '01 ' . xl('Mammogram')
  );
 } else {
  $exams = array(
   "last_breast_exam"               => '00 ' . xl('Breast Exam'),
   "last_cardiac_echo"              => '08 ' . xl('Cardiac Echo'),
   "last_ecg"                       => '07 ' . xl('ECG'),
   "last_gynocological_exam"        => '02 ' . xl('Gynecological Exam'),
   "last_mammogram"                 => '01 ' . xl('Mammogram'),
   "last_physical_exam"             => '05 ' . xl('Physical Exam'),
   "last_prostate_exam"             => '04 ' . xl('Prostate Exam'),
   "last_rectal_exam"               => '03 ' . xl('Rectal Exam'),
   "last_sigmoidoscopy_colonoscopy" => '06 ' . xl('Sigmoid/Colonoscopy'),
   // new for McCormick:
   "last_retinal"                   => '09 ' . xl('Retinal Exam'),
   "last_fluvax"                    => '10 ' . xl('Flu Vaccination'),
   "last_pneuvax"                   => '11 ' . xl('Pneumonia Vaccination'),
   "last_ldl"                       => '12 ' . xl('LDL'),
   "last_hemoglobin"                => '13 ' . xl('Hemoglobin'),
   "last_psa"                       => '14 ' . xl('PSA')
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
