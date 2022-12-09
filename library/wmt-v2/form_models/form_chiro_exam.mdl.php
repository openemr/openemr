<?php
	
	require_once FORM_CLASSES . 'FormModel.class.php';

	class form_chiro_exam_Model extends FormModel
	{
		public function populateMcChoiceLabels()
		{
			$this->getCervicalSpineListModel();
			$this->getShoulderJointListModel();
			$this->getForearmWristAndHandListModel();
			$this->getThoracicSpineListModel();
			$this->getLumbarSpineListModel();
			$this->getPelvisAndSacroiliacListModel();
			$this->getHipJointListModel();
			$this->getKneeJointListModel();
			$this->getLowerLegListModel();
		}

		public function getCervicalSpineListModel()
		{
			return $this->getModelListById('cervical_spine', 'seq');
		}

		public function getShoulderJointListModel()
		{
			return $this->getModelListById('shoulder_joint', 'seq');
		}

		public function getForearmWristAndHandListModel()
		{
			return $this->getModelListById('forearm_wrist_and_hand', 'seq');
		}

		public function getThoracicSpineListModel()
		{
			return $this->getModelListById('thoracic_spine', 'seq');
		}

		public function getLumbarSpineListModel()
		{
			return $this->getModelListById('lumbar_spine', 'seq');
		}

		public function getPelvisAndSacroiliacListModel()
		{
			return $this->getModelListById('pelvis_and_sacroiliac', 'seq');
		}

		public function getHipJointListModel()
		{
			return $this->getModelListById('hip_joint', 'seq');
		}

		public function getKneeJointListModel()
		{
			return $this->getModelListById('knee_joint', 'seq');
		}

		public function getLowerLegListModel()
		{
			return $this->getModelListById('lower_leg_ankle_foot', 'seq');
		}
	}