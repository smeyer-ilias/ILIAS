<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMemberSearchGUI
 * 
 * @author Nadia Matuschek <nmatuschek@databay.de>
 *
**/
class ilMailMemberSearchGUI
{
	/**
	 * @var mixed
	 */
	protected $mail_roles;

	/**
	 * @var ilAbstractMailMemberRoles
	 */
	protected $objMailMemberRoles;
	/**
	 * @var null object ilCourseParticipants || ilGroupParticipants
	 */
	protected $objParticipants = NULL;

	/**
	 * ilMailMemberSearchGUI constructor.
	 * @param                           $ref_id
	 * @param ilAbstractMailMemberRoles $objMailMemberRoles
	 */
	public function __construct($ref_id, ilAbstractMailMemberRoles $objMailMemberRoles)
	{
		global $ilCtrl, $tpl, $lng;
		
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->lng = $lng;
		
		$this->lng->loadLanguageModule('mail');
		$this->lng->loadLanguageModule('search');

		$this->ref_id = $ref_id;
		
		$this->objMailMemberRoles = $objMailMemberRoles;
		$this->mail_roles = $objMailMemberRoles->getMailRoles($ref_id);
	}

	/**
	 * @return bool
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->ctrl->setReturn($this,'');
		
		switch($next_class)
		{
			default:
				switch($cmd)
				{
					case 'sendMailToSelectedUsers':
						$this->sendMailToSelectedUsers();
						break;

					case 'showSelectableUsers':
						$this->showSelectableUsers();
						break;
					
					case 'nextMailForm':
						$this->nextMailForm();
						break;

					default:
						$this->showSearchForm();
						break;
				}	
				break;
		}
		return true;
	}

	/**
	 * 
	 */
	protected function nextMailForm()
	{
		global $lng;
		
		$form = $this->initMailToMembersForm();
		if($form->checkInput())
		{
			if($form->getInput('mail_member_type') == 'mail_member_roles')
			{
				if(count($form->getInput('roles')) > 0)
				{
					require_once 'Services/Mail/classes/class.ilMailFormCall.php';
					$_SESSION['mail_roles'] = $_POST['roles'];
					ilUtil::redirect(ilMailFormCall::getRedirectTarget(
							$this, 'showSearchForm', array('type' => 'role'), array('type' => 'role', 'rcp_to' => implode(',', $_POST['roles']), 'sig' => ''
					)));
				}
				else
				{
					$form->setValuesByPost();
					ilUtil::sendFailure($lng->txt('no_checkbox'));
					return $this->showSearchForm();
				}
			}
			else
			{
				$this->showSelectableUsers();
				return;
			}
		}

		$form->setValuesByPost();
		$this->showSearchForm();
	}

	/**
	 * 
	 */
	protected function showSelectableUsers()
	{
		global $tpl;
		
		include_once './Services/Contact/classes/class.ilMailMemberSearchTableGUI.php';
		include_once './Services/Contact/classes/class.ilMailMemberSearchDataProvider.php';
		
		$tpl->getStandardTemplate();
		$tbl = new ilMailMemberSearchTableGUI($this, 'showSelectableUsers');
		$provider = new ilMailMemberSearchDataProvider($this->getObjParticipants());
		$tbl->setData($provider->getData());
		
		$tpl->setContent($tbl->getHTML());
	}

	/**
	 * @return bool
	 */
	protected function sendMailToSelectedUsers()
	{
		if(!count($_POST['user_ids']))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			$this->showSelectableUsers();
			return false;
		}

		$rcps = array();
		foreach($_POST['user_ids'] as $usr_id)
		{
			$rcps[] = ilObjUser::_lookupLogin($usr_id);
		}

		if(!count(array_filter($rcps)))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			$this->showSelectableUsers();
			return false;
		}

		require_once 'Services/Mail/classes/class.ilMailFormCall.php';
		ilUtil::redirect(ilMailFormCall::getRedirectTarget(
			$this, 'members', array(),
			array('type' => 'new', 'rcp_to' => implode(',', $rcps), 'sig' => '')));
		return true;
	}
	
	/**
	 * 
	 */
	protected function showSearchForm()
	{
		global $tpl;
		
		$tpl->getStandardTemplate();
		
		$form = $this->initMailToMembersForm();
		$tpl->setContent($form->getHTML());
	}

	/**
	 * @return null
	 */
	protected function getObjParticipants()
	{
		return $this->objParticipants;
	}

	/**
	 * @param null $objParticipants ilCourseParticipants || ilGroupParticipants
	 */
	public function setObjParticipants($objParticipants)
	{
		$this->objParticipants = $objParticipants;
	}
	
	/**
	 * @return ilPropertyFormGUI
	 */
	protected function initMailToMembersForm()
	{
		$this->lng->loadLanguageModule('mail');

		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('mail_members'));

		$form->setFormAction($this->ctrl->getFormAction($this, 'nextMailForm'));

		$radio_grp = $this->getMailRadioGroup();

		$form->addItem($radio_grp);
		$form->addCommandButton('nextMailForm', $this->lng->txt('continue'));
		$form->addCommandButton('members', $this->lng->txt('cancel'));

		return $form;
	}

	/**
	 * @return mixed
	 */
	private function getMailRoles()
	{
		return $this->mail_roles;
	}
	
	/**
	 * @return ilRadioGroupInputGUI
	 */
	protected function getMailRadioGroup()
	{
		$mail_roles = $this->getMailRoles();
		
		$radio_grp   = new ilRadioGroupInputGUI('', 'mail_member_type');
		$radio_roles = new ilRadioOption($this->objMailMemberRoles->getRadioOptionTitle(), 'mail_member_roles');
		foreach($mail_roles as $role)
		{
			$chk_role     = new ilCheckboxInputGUI($role['form_option_title'], 'roles['.$role['mailbox'].']');
			$chk_role->setValue($role['mailbox']);
			$radio_roles->addSubItem($chk_role);
		}

		$radio_sel_users = new ilRadioOption($this->lng->txt('mail_sel_users'), 'mail_sel_users');

		$radio_grp->setValue('mail_member_roles');
		$radio_grp->addOption($radio_roles);
		$radio_grp->addOption($radio_sel_users);
		
		return $radio_grp;
	}
}