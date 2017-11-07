<?php

/**
 * Record state action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_State_Action extends Vtiger_Action_Controller
{

	/**
	 * Record model instance
	 * @var Vtiger_Record_Model
	 */
	protected $record = false;

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$this->record = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		if ($request->getByType('state') === 'Archived' && !$this->record->privilegeToArchive()) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if ($request->getByType('state') === 'Deleted' && !$this->record->privilegeToDelete()) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if ($request->getByType('state') === 'Active' && !$this->record->privilegeToActivate()) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Main process
	 * @param \App\Request $request
	 * @return \Vtiger_Response
	 */
	public function process(\App\Request $request)
	{
		if (!in_array($request->getByType('state'), ['Active', 'Deleted', 'Archived'])) {
			throw new \App\Exceptions\NoPermitted('ERR_ILLEGAL_VALUE', 406);
		}
		$this->record->changeState($request->getByType('state'));
		if ($request->getByType('sourceView') === 'List') {
			$response = new Vtiger_Response();
			$response->setResult(['notify' => ['text' => \App\Language::translate('LBL_CHANGES_SAVED')]]);
			$response->emit();
		} else {
			header("Location: index.php?module={$request->getModule()}&view=Detail&record={$request->getInteger('record')}");
		}
	}
}
