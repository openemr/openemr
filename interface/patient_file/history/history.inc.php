<?
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
   "last_cardiac_echo"              => '08 Cardiac Echo',
   "last_ecg"                       => '07 ECG',
   "last_physical_exam"             => '05 Physical Exam',
   "last_breast_exam"               => '00 Breast Exam',
   "last_mammogram"                 => '01 Mammogram'
  );
 } else {
  $exams = array(
   "last_breast_exam"               => '00 Breast Exam',
   "last_cardiac_echo"              => '08 Cardiac Echo',
   "last_ecg"                       => '07 ECG',
   "last_gynocological_exam"        => '02 Gynecological Exam',
   "last_mammogram"                 => '01 Mammogram',
   "last_physical_exam"             => '05 Physical Exam',
   "last_prostate_exam"             => '04 Prostate Exam',
   "last_rectal_exam"               => '03 Rectal Exam',
   "last_sigmoidoscopy_colonoscopy" => '06 Sigmoid/Colonoscopy'
  );
 }

 // Deprecated surgery date items that should be shown only if there
 // is data, and which should be moved to the lists table:
 //
 $obsoletes = array(
  'cataract_surgery' => 'Cataract Surgery',
  'tonsillectomy'    => 'Tonsillectomy',
  'appendectomy'     => 'Appendectomy',
  'cholecystestomy'  => 'Cholecystestomy',
  'heart_surgery'    => 'Heart Surgery',
  'hysterectomy'     => 'Hysterectomy',
  'hernia_repair'    => 'Hernia Repair',
  'hip_replacement'  => 'Hip Replacement',
  'knee_replacement' => 'Knee Replacement'
 );
?>
