@api @mailhog
Feature: notifications-content

	Background:
		Given these users have been created:
			|username|password|displayname|email       |
			|user1   |1234    |User One   |u1@oc.com.np|
		And using API version "2"

	Scenario: Create notification
		When the user "user1" sets the email notification option to "always" using the API
		And user "user1" is sent a notification with
			| app         | notificationsacceptancetesting                                            |
			| timestamp   | 144958517                                                                 |
			| subject     | Acceptance Testing                                                        |
			| link        | https://owncloud.org/blog/about-activities-and-notifications-in-owncloud/ |
			| message     | About Activities and Notifications in ownCloud                            |
			| object_type | blog                                                                      |
			| object_id   | 9483                                                                      |
		Then the email address "u1@oc.com.np" should have received an email with the body containing
			"""
			About Activities and Notifications in ownCloud
			
			See https://owncloud.org/blog/about-activities-and-notifications-in-owncloud/ for more information
			"""
		And the email address "u1@oc.com.np" should have received an email with the body containing
			"""
			See <a href="https://owncloud.org/blog/about-activities-and-notifications-in-owncloud/">https://owncloud.org/blog/about-activities-and-notifications-in-owncloud/</a> for more information</td>
			"""