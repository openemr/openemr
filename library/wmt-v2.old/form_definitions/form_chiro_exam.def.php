<?php 

	require_once FORM_CLASSES . 'FormDefinition.class.php';

	class form_chiro_exam_Definition extends FormDefinition
	{
		public $fileName = 'form_chiro_exam';
		public $prefix   = 'chiro_';
		// this is the array that defines my subsections for the main view and 
		// the summary. The key names are the file names of the subviews for 
		// each subsection. This is completely optional to set it up like this.
		// If you go into the FormDefinition file you'll see that this array
		// is not set as part of the compulsary set or properties.
		public $subSectionTitles = array(
			'chiro_cervical_spine'        => 'Cervical Spine',
			'chiro_left_shoulder_joint'   => 'Left Shoulder Joint',
			'chiro_right_shoulder_joint'  => 'Right Shoulder Joint',
			'chiro_left_lower_arm'        => 'Left Forearm Wrist and Hand',
			'chiro_right_lower_arm'       => 'Right Forearm Wrist and Hand',
			'chiro_thoracic_spine'        => 'Thoracic Spine',
			'chiro_lumbar_spine'          => 'Lumbar Spine',
			'chiro_pelvis_and_sacroiliac' => 'Pelvis and Sacroiliac',
			'chiro_left_hip_joints'       => 'Left Hip Joint',
			'chiro_right_hip_joints'      => 'Right Hip Joint',
			'chiro_left_knee_joints'      => 'Left Knee Joint',
			'chiro_right_knee_joints'     => 'Right Knee Joint',
			'chiro_left_lower_leg'        => 'Left Lower Ankle, Foot',
			'chiro_right_lower_leg'       => 'Right Lower Ankle, Foot'
		);
		public $multipleChoiceDefs = array(
			'2' => 'Positive',
			'1' => 'Negative',
			'3' => 'Inconclusive'
		);
	}
