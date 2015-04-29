@totara @block_facetoface
Feature: Confirm Sessions show up in my face to face sessions
  In order for the my sessions page is correct
  As an admin
  I need to be able to see and create sessions

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | learner1  | Learner | 1 | learner@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | learner1 | C1 | student |
    And I log in as "admin"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Face-to-face" to section "1" and I fill the form with:
      | Name | Test session |
      | Description | Test session |
    And I follow "Test session"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | Session date/time known | Yes          |
      | timestart[0][day]       | 1            |
      | timestart[0][month]     | 1            |
      | timestart[0][year]      | 2020         |
      | timestart[0][hour]      | 11           |
      | timestart[0][minute]    | 00           |
      | timefinish[0][day]      | 1            |
      | timefinish[0][month]    | 1            |
      | timefinish[0][year]     | 2020         |
      | timefinish[0][hour]     | 12           |
      | timefinish[0][minute]   | 00           |
      | Capacity                | 20           |
      | Details                 | some details |
    And I click on "Save changes" "button"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | Session date/time known | Yes               |
      | timestart[0][day]       | 2                 |
      | timestart[0][month]     | 1                 |
      | timestart[0][year]      | 2020              |
      | timestart[0][hour]      | 11                |
      | timestart[0][minute]    | 00                |
      | timefinish[0][day]      | 2                 |
      | timefinish[0][month]    | 1                 |
      | timefinish[0][year]     | 2020              |
      | timefinish[0][hour]     | 12                |
      | timefinish[0][minute]   | 00                |
      | Capacity                | 20                |
      | Details                 | some more details |
    And I click on "Save changes" "button"
    And I follow "C1"
    And I add a "Face-to-face" to section "2" and I fill the form with:
      | Name        | Test session 2              |
      | Description | Test session 2 description  |
    And I follow "Test session 2"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | Session date/time known | Yes            |
      | timestart[0][day]       | 1              |
      | timestart[0][month]     | 2              |
      | timestart[0][year]      | 2020           |
      | timestart[0][hour]      | 11             |
      | timestart[0][minute]    | 00             |
      | timefinish[0][day]      | 1              |
      | timefinish[0][month]    | 2              |
      | timefinish[0][year]     | 2020           |
      | timefinish[0][hour]     | 12             |
      | timefinish[0][minute]   | 00             |
      | Capacity                | 30             |
      | Details                 | 1 some details |
    And I click on "Save changes" "button"
    And I follow "Add a new session"
    And I set the following fields to these values:
      | Session date/time known | Yes                 |
      | timestart[0][day]       | 2                   |
      | timestart[0][month]     | 2                   |
      | timestart[0][year]      | 2020                |
      | timestart[0][hour]      | 11                  |
      | timestart[0][minute]    | 00                  |
      | timefinish[0][day]      | 2                   |
      | timefinish[0][month]    | 2                   |
      | timefinish[0][year]     | 2020                |
      | timefinish[0][hour]     | 12                  |
      | timefinish[0][minute]   | 00                  |
      | Capacity                | 30                  |
      | Details                 | 2 some more details |
    And I click on "Save changes" "button"
    And I follow "My Learning"
    And I click on "Customise this page" "button"
    And I add the "Face-to-face" block
    And I click on "Stop customising this page" "button"

  #@javascript
  Scenario: Test filters
    Given I follow "Upcoming sessions"
    And I set the following fields to these values:
      | from[day]   | 1    |
      | from[month] | 1    |
      | from[year]  | 2019 |
      | to[enabled] | 0    |
    And I click on "Apply" "button"
    And I should see "Test session"
    And I should see "Test session 2"
    And I set the following fields to these values:
      | to[enabled] | 1    |
      | to[day]     | 1    |
      | to[month]   | 2    |
      | to[year]    | 2020 |
    And I click on "Apply" "button"
    And I should see "Test session"
    And I should not see "Test session 2"