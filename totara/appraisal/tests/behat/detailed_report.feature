@javascript @totara @totara_appraisal
Feature: Test appraisal detailed report with numeric question
  In order to ensure the appraisals works as expected
  As an admin
  I need to create calendar data

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email       | auth   | confirmed |
      | user1    | User      | One      | one@example.invalid | manual | 1         |
      | user2    | User      | Two      | two@example.invalid | manual | 1         |
    And the following "cohorts" exist:
      | name     | idnumber |
      | Cohort 1 | CH1      |
    And I log in as "admin"
    And I add "User One (one@example.invalid)" user to "CH1" cohort members
    And I add "User Two (two@example.invalid)" user to "CH1" cohort members

    # Set manager for User One
    And I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I click on "User One" "link"
    And I navigate to "Primary position" node in "Profile settings for User One > Positions"
    And I press "Choose manager"
    And I click on "Admin User" "link" in the "Choose manager" "totaradialogue"
    And I click on "OK" "button" in the "Choose manager" "totaradialogue"
    And I press "Update position"

    # Set manager for User Two
    And I navigate to "Browse list of users" node in "Site administration > Users > Accounts"
    And I click on "User Two" "link"
    And I navigate to "Primary position" node in "Profile settings for User Two > Positions"
    And I press "Choose manager"
    And I click on "Admin User" "link" in the "Choose manager" "totaradialogue"
    And I click on "OK" "button" in the "Choose manager" "totaradialogue"
    And I press "Update position"


  Scenario: Create Appraisal with assigned audience and check detailed report
    # Create appraisal with stage and page
    And I navigate to "Manage appraisals" node in "Site administration > Appraisals"
    And I press "Create appraisal"
    And I set the following fields to these values:
      | Name        | Behat Test Appraisal          |
      | Description | This is the behat description |
    And I press "Create appraisal"
    And I press "Add stage"
    And I set the following fields to these values:
      | Name                  | Behat Appraisal stage   |
      | Description           | Behat stage description |
      | timedue[enabled]      | 1                       |
      | timedue[day]          | 31                      |
      | timedue[month]        | 12                      |
      | timedue[year]         | 2037                    |
      | Page names (optional) | Page1.1                 |
    And I click on "Add stage" "button" in the ".fitem_actionbuttons" "css_element"
    And I should see "Behat Appraisal stage" in the ".appraisal-stages" "css_element"
    And I click on "Behat Appraisal stage" "link" in the ".appraisal-stages" "css_element"

    # Create appraisal numeric rating question
    And I click on "Page1.1" "link" in the ".appraisal-page-list" "css_element"
    And I set the field "id_datatype" to "Rating (numeric scale)"
    And I click on "Add" "button" in the "#fgroup_id_addquestgroup" "css_element"
    And I set the following fields to these values:
      | Question     | Rating question  |
      | From         | 1                |
      | To           | 10               |
      | list         | Text input field |
      | id_roles_1_2 | 1                |
      | id_roles_1_1 | 1                |
      | id_roles_2_2 | 1                |
      | id_roles_2_1 | 1                |
    # Display settings doesn't visually change to "Text input field" radio. So nail it.
    And I click on "#id_list_2" "css_element"

    And I click on "//button[text()='Save changes']" "xpath_element" in the "div.moodle-dialogue-focused div.moodle-dialogue-ft" "css_element"
    And I click on "Assignments" "link"
    And I set the field "menugroupselector" to "Audience"
    And I click on "Cohort 1" "link" in the "Assign Learner Group To Appraisal" "totaradialogue"
    And I click on "Save" "button" in the "Assign Learner Group To Appraisal" "totaradialogue"
    And I click on "Activate now" "link"
    And I press "Activate"

    # Edit report to add "All roles"
    When I navigate to "Reports" node in "Site administration > Appraisals"
    And I click on "Detail report" "link" in the "Behat Test Appraisal" "table_row"
    And I press "Edit this report"
    And I click on "Columns" "link"
    And I set the field "id_newcolumns" to "All Roles' Score"
    And I press "Save changes"
    And I log out

    # Add data for User One
    When I log in as "user1"
    And I click on "Appraisal" in the totara menu
    And I press "Start"
    And I set the following fields to these values:
      | Your answer | 3 |
    And I press "Complete Stage"
    And I log out

    # Load report with new column
    When I log in as "admin"
    And I navigate to "Reports" node in "Site administration > Appraisals"
    And I click on "Detail report" "link" in the "Behat Test Appraisal" "table_row"
    And I should see "3" in the "User One" "table_row"
    And I should not see "3" in the "User Two" "table_row"