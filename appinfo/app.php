<?php

namespace OCA\Contacts;
use \OCA\AppFramework\Core\API;

//require_once __DIR__ . '/../controller/groupcontroller.php';
\Sabre\VObject\Component::$classMap['VCARD']	= '\OCA\Contacts\VObject\VCard';
\Sabre\VObject\Property::$classMap['CATEGORIES'] = 'OCA\Contacts\VObject\GroupProperty';
\Sabre\VObject\Property::$classMap['FN']		= '\OC\VObject\StringProperty';
\Sabre\VObject\Property::$classMap['TITLE']		= '\OC\VObject\StringProperty';
\Sabre\VObject\Property::$classMap['ROLE']		= '\OC\VObject\StringProperty';
\Sabre\VObject\Property::$classMap['NOTE']		= '\OC\VObject\StringProperty';
\Sabre\VObject\Property::$classMap['NICKNAME']	= '\OC\VObject\StringProperty';
\Sabre\VObject\Property::$classMap['EMAIL']		= '\OC\VObject\StringProperty';
\Sabre\VObject\Property::$classMap['TEL']		= '\OC\VObject\StringProperty';
\Sabre\VObject\Property::$classMap['IMPP']		= '\OC\VObject\StringProperty';
\Sabre\VObject\Property::$classMap['URL']		= '\OC\VObject\StringProperty';
\Sabre\VObject\Property::$classMap['N']			= '\OC\VObject\CompoundProperty';
\Sabre\VObject\Property::$classMap['ADR']		= '\OC\VObject\CompoundProperty';
\Sabre\VObject\Property::$classMap['GEO']		= '\OC\VObject\CompoundProperty';

// dont break owncloud when the appframework is not enabled
if(\OCP\App::isEnabled('appframework')) {
	$api = new API('contacts');

	$api->addNavigationEntry(array(
		'id' => 'contacts_index',
		'order' => 10,
		'href' => \OC_Helper::linkToRoute('contacts_index'),
		'icon' => \OCP\Util::imagePath( 'contacts', 'contacts.svg' ),
		'name' => \OC_L10N::get('contacts')->t('Contacts')
		)
	);

	$api->connectHook('OC_User', 'post_createUser', '\OCA\Contacts\Hooks', 'userCreated');
	$api->connectHook('OC_User', 'post_deleteUser', '\OCA\Contacts\Hooks', 'userDeleted');
	$api->connectHook('OCA\Contacts', 'pre_deleteAddressBook', '\OCA\Contacts\Hooks', 'addressBookDeletion');
	$api->connectHook('OCA\Contacts', 'pre_deleteContact', '\OCA\Contacts\Hooks', 'contactDeletion');
	$api->connectHook('OCA\Contacts', 'post_createContact', 'OCA\Contacts\Hooks', 'contactUpdated');
	$api->connectHook('OCA\Contacts', 'post_updateContact', '\OCA\Contacts\Hooks', 'contactUpdated');
	$api->connectHook('OCA\Contacts', 'scanCategories', '\OCA\Contacts\Hooks', 'scanCategories');
	$api->connectHook('OCA\Contacts', 'indexProperties', '\OCA\Contacts\Hooks', 'indexProperties');

	\OCP\Util::addscript('contacts', 'loader');

	\OC_Search::registerProvider('OCA\Contacts\SearchProvider');
	//\OCP\Share::registerBackend('contact', 'OCA\Contacts\Share_Backend_Contact');
	\OCP\Share::registerBackend('addressbook', 'OCA\Contacts\Share\Addressbook', 'contact');


	if(\OCP\User::isLoggedIn()) {
		$app = new App($api->getUserId());
		$addressBooks = $app->getAddressBooksForUser();
		foreach($addressBooks as $addressBook)  {
			if($addressBook->getBackend()->name === 'local') {
				\OCP\Contacts::registerAddressBook(new AddressbookProvider($addressBook));
			}
		}
	}
} else {
	\OCP\Util::writeLog('contacts', 'AppFramework is not enabled. App is not functional!', \OCP\Util::ERROR);
}
