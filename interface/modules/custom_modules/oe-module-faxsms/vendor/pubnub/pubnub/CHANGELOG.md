## v6.3.0
June 18 2024

#### Added
- Added support for file sharing operations.

## v6.2.1
June 11 2024

#### Fixed
- Fix value for FCM push type provisioning key.

## v6.2.0
June 11 2024

#### Added
- Replacing GCM with FCM. This is not a breaking change, but using GCM will result in throwing `E_USER_DEPRECATED` warning.

## v6.1.3
November 27 2023

#### Fixed
- Gracefully handle decrypting an unencrypted method. If a decryption error occurs when trying to decrypt plain text, the plain text message will be returned and an error field will be set in the response. This works for both history and subscription messages.

## v6.1.2
November 02 2023

#### Modified
- Fix license info in composer.json

## v6.1.1
October 30 2023

#### Fixed
- Changed license type from MIT to PubNub Software Development Kit License.

## v6.1.0
October 16 2023

#### Added
- Add crypto module that allows configure SDK to encrypt and decrypt messages.

#### Fixed
- Improved security of crypto implementation by adding enhanced AES-CBC cryptor.

## v6.0.1
May 18 2023

#### Fixed
- Support for Monolog/Monolog@^3.0.
- Added replacement for deprecated utf8_decode method.

## v6.0.0
February 01 2023

#### Modified
- BREAKING CHANGES: This update is intended to bring compatibility with PHP 8.2 and newer versions of dependencies.

## v5.1.0
August 30 2022

#### Added
- Add option to initialize PubNub with UserId.

## v5.0.0
January 26 2022

#### Modified
- BREAKING CHANGES: Disable automated uuid generation and make it mandatory to specify before `PubNub` instance creation.

## v4.7.0
December 16 2021

#### Added
- RevokeToken method.

#### Fixed
- Fixed error in Signal request.

## [v4.6.0](https://github.com/pubnub/php/releases/tag/v4.6.0)
October-26-2021

- 🌟️ Add support for Access Manager v3 with example. 

## [v4.5.0](https://github.com/pubnub/php/releases/tag/v4.5.0)
August-24-2021

- 🌟️ Missing PNPresenceEventResult getters added, dependency update. 

## [v4.4.0](https://github.com/pubnub/php/releases/tag/v4.4.0)
July-29-2021

- 🌟️ Fix for wrong signature calculation mechanism added. 

## [v4.3.0](https://github.com/pubnub/php/releases/tag/v4.3.0)
March-29-2021

- 🌟️ Add support for random initialization vector. 

## [v4.2.0](https://github.com/pubnub/php/releases/tag/v4.2.0)
February-2-2021

- 🌟️ Add support for device channel registration with apns2. 
- 🌟️ Allows management of users and channels with metadata. 
- 🌟️ Implement v2 signatures required for push and objects. 
- 🌟️ Implement v2 grant endpoint with support for user level grant. 

## [v4.1.7](https://github.com/pubnub/php/releases/tag/v4.1.7)
September-14-2020

- 🌟️ Add delete permission support to grant call. 

## [v4.1.6](https://github.com/pubnub/php/releases/tag/v4.1.6)
August-20-2020

- ⭐️️ Remove hard coded keys from tests. 

## [v4.1.5](https://github.com/pubnub/php/tree/v4.1.5)
 October-22-2019

- ⭐Update composer.json package version constraints
- ⭐Update .travis.yml to run tests for PHP versions 7.2 and 7.3

## [v4.1.4](https://github.com/pubnub/php/tree/v4.1.4)
 October-18-2019

- 🐛Add support for request transport reusing to resolve slow publish issues when multiple messages are published consecutively.
- 🐛Drop support for HHVM.

## [v4.1.3](https://github.com/pubnub/php/tree/v4.1.3)
 February-28-2019

- ⭐Add messageCounts() method for retrieving unread message count

## [v4.1.2](https://github.com/pubnub/php/tree/v4.1.2)
 October-23-2018

- 🐛Fix issue with deleteMessages endpoint using GET HTTP method instead of DELETE

## [v4.1.1](https://github.com/pubnub/php/tree/v4.1.1)
 October-2-2018

- ⭐Add setOrigin method
- ⭐Add .gitattributes file to save space when using composer
- 🐛Fix urlencode issue with channel names
- 🐛Fix channel name validation for Publish
- 🐛Return class instance of PNConfiguration on setConnectTimeout method

## [v4.1.0](https://github.com/pubnub/php/tree/v4.1.0)
 September-7-2018

- ⭐Add fire() method on PubNub instance
- 🐛Change return value of SetState::getOperationType
- ⭐Add history delete (deleteMessages) method on PubNub instance
- ⭐Add Telemetry Manager

## [v4.0.0](https://github.com/pubnub/php/tree/v4.0.0)
 June-8-2017

- 🐛Fix publish sequence counter
- 🐛Fix publish tests
- 🐛Release final SDK

## [v4.0.0-beta.3](https://github.com/pubnub/php/tree/v4.0.0-beta.3)
 May-5-2017

- 🐛Fix special characters encoding
- 🐛Remove set* prefix from publish setters

## [v4.0.0-beta.2](https://github.com/pubnub/php/tree/v4.0.0-beta.2)
 April-21-2017

- 🐛Add missing methods on PubNub instance
- 🐛Fix removeAllPushChannelsForDevice method case

## [v4.0.0-beta](https://github.com/pubnub/php/tree/v4.0.0-beta)
 April-18-2017

- 🐛Fix windows compatibility
- ⭐Add option to disable SSL
- 🐛Fix subscribe presence response parsing
- 🐛Add missing removeListener()
- ⭐Add logger
- 🐛Fix json decoding error
- ⭐Add Push methods


## [v4.0.0-alpha](https://github.com/pubnub/php/tree/v4.0.0-alpha)
 April-5-2017

- ⭐Alpha Release
