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
 * Custom Elgg app OAuth2 provider adapter.
 */
class ElggApp extends OAuth2
{
    public function getUserProfile()
    {
        $response = $this->apiRequest('oauth/me');

        $data = new Data\Collection($response);

        if (!$data->exists('username')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier = $data->get('username');
        $userProfile->displayName = $data->get('name') ?: $data->get('username');
        $userProfile->email = $data->get('email');
        $userProfile->description = $data->get('description');
        $userProfile->language = $data->get('language');
        $userProfile->photoURL = $data->get('photo_url');
        $userProfile->webSiteURL = $data->get('website_url');
        $userProfile->address = $data->get('address');
        $userProfile->phone = $data->get('phone');
        $userProfile->data = [
            'username' => $data->get('username'),
            'contactemail' => $data->get('contactemail'),
        ];
        $userProfile->emailVerified = $data->get('email_verified') ? $data->get('email') : '';

        return $userProfile;
    }
}
