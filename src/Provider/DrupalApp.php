<?php
/**
 * Elgg OAuth Plugin [Plugin]
 * @author Nikolai Shcherbin
 * @package Plugin
 * @license GNU Affero General Public License version 3
 * @copyright (c) Nikolai Shcherbin 2021
 * @link https://wzm.me
**/

namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;

/**
 * Custom Drupal app OAuth2 provider adapter.
 */
class DrupalApp extends OAuth2 {
	
	/**
     * {@inheritdoc}
     */
    protected $scope = 'openid profile email';
	
	public function getUserProfile() {
        $response = $this->apiRequest('oauth2/UserInfo');

        $data = new Data\Collection($response);

        if (!$data->exists('sub')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier = $data->get('sub');
        $userProfile->displayName = $data->get('name') ?: $data->get('preferred_username');
        $userProfile->photoURL = $data->get('picture');
        $userProfile->email = $data->get('email');
        $userProfile->emailVerified = $data->get('email_verified') ? $data->get('email') : '';
		$userProfile->data = [
			'username' => $data->get('preferred_username'),
        ];

        return $userProfile;
    }

}
