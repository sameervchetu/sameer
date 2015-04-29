@totara @totara_coursecatalog
Feature: Users can expand the course info in course catalog
  In order to expand course info
  As a user
  I need to login if forcelogin enabled

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | 1 | student1@asd.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And I log in as "admin"
    And I set the following administration settings values:
      | Enhanced catalog | 1 |
    And I press "Save changes"
    And I log out

  @javascript
  Scenario: Allow not logged in users to expand catalog when forcelogin disabled
    Given I follow "Find Learning"
    And I click on ".rb-display-expand" "css_element"
    Then I should see "Course summary"

  @javascript
  Scenario: Allow guest account to expand catalog when forcelogin enabled
    Given I log in as "admin"
    And I set the following administration settings values:
      | forcelogin | 1 |
    And I log out
    And I click on "#guestlogin input[type=submit]" "css_element"
    And I follow "Find Learning"
    And I click on ".rb-display-expand" "css_element"
    Then I should see "Course summary"

  @javascript
  Scenario: Allow user to expand catalog when forcelogin enabled
    Given I log in as "admin"
    And I set the following administration settings values:
      | forcelogin | 1 |
    And I log out
    And I click on "#guestlogin input[type=submit]" "css_element"
    And I log in as "student1"
    And I follow "Find Learning"
    And I click on ".rb-display-expand" "css_element"
    Then I should see "Course summary"
