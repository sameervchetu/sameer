@totara @totara_coursecatalog @enrol @enrol_self
Feature: Users can auto-enrol themself via course catalog in courses where self enrolment is allowed
  In order to participate in courses
  As a user
  I need to auto enrol me in courses

  Background:
    Given I log in as "admin"
    And I set the following administration settings values:
      | Enhanced catalog | 1 |
    And I press "Save changes"
    And I log out
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@asd.com |
      | student1 | Student | 1 | student1@asd.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |


  @javascript
  Scenario: Self-enrolment through course catalog requiring a group enrolment key
    Given I log in as "teacher1"
    And I follow "Course 1"
    When I add "Self enrolment" enrolment method with:
      | Custom instance name | Test student enrolment |
      | Enrolment key | moodle_rules |
      | Use group enrolment keys | Yes |
    And I follow "Groups"
    And I press "Create group"
    And I set the following fields to these values:
      | Group name | Group 1 |
      | Enrolment key | Test-groupenrolkey1 |
    And I press "Save changes"
    And I log out
    And I log in as "student1"
    And I follow "Find Learning"
    And I click on ".rb-display-expand" "css_element"
    And I press "Enrol"
    Then I should see "Incorrect enrolment key, please try again"
    And I set the following fields to these values:
      | Enrolment key | Test-groupenrolkey1 |
    And I press "Enrol"
    Then I should see "Topic 1"
    And I should not see "Enrolment options"
    And I should not see "Enrol me in this course"
