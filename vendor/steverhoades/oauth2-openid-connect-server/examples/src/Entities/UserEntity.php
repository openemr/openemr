<?php

namespace OpenIDConnectServerExamples\Entities;

use OpenIDConnectServer\Entities\ClaimSetInterface;

class UserEntity extends \OAuth2ServerExamples\Entities\UserEntity implements ClaimSetInterface
{
    public function getClaims()
    {
        return [
            // profile
            'name' => 'John Smith',
            'family_name' => 'Smith',
            'given_name' => 'John',
            'middle_name' => 'Doe',
            'nickname' => 'JDog',
            'preferred_username' => 'jdogsmith77',
            'profile' => '',
            'picture' => 'avatar.png',
            'website' => 'http://www.google.com',
            'gender' => 'M',
            'birthdate' => '01/01/1990',
            'zoneinfo' => '',
            'locale' => 'US',
            'updated_at' => '01/01/2018',
            // email
            'email' => 'john.doe@example.com',
            'email_verified' => true,
            // phone
            'phone_number' => '(866) 555-5555',
            'phone_number_verified' => true,
            // address
            'address' => '50 any street, any state, 55555',
        ];
    }
}
