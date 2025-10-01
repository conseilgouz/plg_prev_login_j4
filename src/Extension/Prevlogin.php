<?php
/**
* Prev Login Plugin  - Joomla 4.x/5.x/6.x Plugin 
* Package			: Prev Login
* copyright 		: Copyright (C) 2025 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*/

namespace ConseilGouz\Plugin\User\Prevlogin\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * An example custom profile plugin.
 *
 * @since  1.6
 */
final class Prevlogin extends CMSPlugin
{
    use DatabaseAwareTrait;

	public function onUserLogin($user, $options = array())
	{
		$user	= $this->_getUser($user, $options);
		if ($user->id)
		{
			try
			{
				$db = Factory::getContainer()->get(DatabaseInterface::class);
		        $query = $db->getQuery(true)
		        ->delete('#__user_profiles')
				->where('user_id = '.$user->id.' AND profile_key LIKE \'profile_prevlogin\'');
		        $db->setQuery($query);
		        $result = $db->execute();
		        $query = $db->getQuery(true)
				->select('lastvisitDate')
				->from('#__users')
				->where('id =' . (int) $user->id);
		        $db->setQuery($query);
				$last = $db->loadObject();
				$lastvisit = "";
				if ($last->lastvisitDate) {
				    $lastvisit = HTMLHelper::date($last->lastvisitDate,'d/m/Y H:i:s');
				}
				$order	= 1;
				$columns = array('user_id', 'profile_key', 'profile_value', 'ordering');
				$values = array($user->id,$db->quote('profile_prevlogin'),$db->quote($lastvisit), $order++);
				$query = $db->getQuery(true)
				->insert('#__user_profiles')
				->columns($db->quoteName($columns))
				->values(implode(', ', $values));
				$db->setQuery($query);
				$db->execute();
				}
			catch (ExecutionFailureException $e) {
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}
 		return true;
	}
	/**
	 * Remove all user profile information for the given user ID
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param	array		$user		Holds the user data
	 * @param	boolean		$success	True if user was succesfully stored in the database
	 * @param	string		$msg		Message
	 */
	function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success) {
			return false;
		}
 
		$userId	= ArrayHelper::getValue($user, 'id', 0, 'int');
 
		if ($userId)
		{
			try
			{
				$db = Factory::getContainer()->get(DatabaseInterface::class);
		        $query = $db->getQuery(true)
		        ->delete('#__user_profiles')
				->where('user_id = '.$userId .' AND profile_key LIKE "profile_prevlogin"');
 		        $db->setQuery($query);
		        $result = $db->execute();
			}
			catch (ExecutionFailureException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}
 
		return true;
	}
 	/**
	 * This method will return a user object
	 *
	 * @param   array  $user     Holds the user data.
	 * @param   array  $options  Array holding options (remember, autoregister, group).
	 *
	 * @return  JUser
	 *
	 * @since   1.5
	 */
	protected function _getUser($user, $options = array())
	{
		$instance = User::getInstance();
		$id = (int) UserHelper::getUserId($user['username']);

		if ($id)
		{
			$instance->load($id);

			return $instance;
		}
		return $instance;
	}
 
}
