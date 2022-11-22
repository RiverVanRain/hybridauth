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
 * Custom Mattermost App OAuth2 provider adapter.
 */
class MattermostApp extends OAuth2 {
	
	public function getUserProfile() {
        $response = $this->apiRequest('users/me');

        $data = new Data\Collection($response);

        if (!$data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }
		
		$url = rtrim($this->apiBaseUrl, '/');

        $userProfile = new User\Profile();

        $userProfile->identifier = $data->get('id');
        $userProfile->displayName = $data->get('nickname') ?: $data->get('username');
		$userProfile->email = $data->get('email');
		$userProfile->firstName = $data->get('first_name');
		$userProfile->lastName = $data->get('last_name');
		$userProfile->language = $data->get('locale');
		$userProfile->photoURL = $data->get('last_picture_update') ? $url . '/users/' . $data->get('id') . '/image?time=' . $data->get('last_picture_update') : '';
		$userProfile->data = [
			'username' => $data->get('username'),
        ];
		$userProfile->emailVerified = $data->get('email_verified') ? $data->get('email') : '';

        return $userProfile;
    }

}
