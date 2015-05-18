@javascript @totara @totara_dashboard
Feature: test default home page feature with dashboards
  In order to test the dashboard setting on the default home page
  I must log in as an admin and set the defaulthomepage setting
  As a student I log in

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | One | student.one@local.host |
    And the following "cohorts" exist:
      | name | idnumber |
      | Cohort 1 | CH1 |
    And I log in as "admin"
    And I follow "Turn editing on"
    And I add a "label" to section "1"
    And I should see "Adding a new Label"
    And I set the following fields to these values:
      | Label text | I'm a label on the frontpage |
    And I press "Save and return to course"
    And I should see "I'm a label on the frontpage"
    And I add "Student One (student.one@local.host)" user to "CH1" cohort members
    And I log out

  Scenario: the default defaulthomepage setting works as expected
    Given I log in as "student1"
    Then I should see "I'm a label on the frontpage"

  Scenario: setting defaulthomepage to My learning works as expected
    Given I log in as "admin"
    And I set the following administration settings values:
      | defaulthomepage | My Learning |
    And I log out
    When I log in as "student1"
    Then I should see "Course overview" in the "#region-main" "css_element"
    And I click on "Site home" "link"
    And I should see "I'm a label on the frontpage"

  Scenario: setting deafulthomepage to Dashboard works as expected
    Given I log in as "admin"
    And I navigate to "Dashboards" node in "Site administration > Appearance"
    # Add a dashboard.
    And I press "Create dashboard"
    And I set the following fields to these values:
     | Name | My first dashboard |
     | Published | 1             |
    And I press "Assign new audiences"
    And I click on "Cohort 1" "link"
    And I press "OK"
    And I wait "1" seconds
    And I press "Create dashboard"
    # Add a second dashboard.
    And I press "Create dashboard"
    And I set the following fields to these values:
      | Name | My second dashboard |
      | Published | 1             |
    And I press "Assign new audiences"
    And I click on "Cohort 1" "link"
    And I press "OK"
    And I wait "1" seconds
    And I press "Create dashboard"
    # Add content to the first dashboard.
    And I click on "My first dashboard" "link"
    And I add the "HTML" block
    And I configure the "(new HTML block)" block
    And I set the field "Block title" to "First dashboard block header"
    And I set the field "Content" to "First dashboard block content"
    And I press "Save changes"
    # Add content to the second dashboard.
    And I navigate to "Dashboards" node in "Site administration > Appearance"
    And I click on "My second dashboard" "link"
    And I add the "HTML" block
    And I configure the "(new HTML block)" block
    And I set the field "Block title" to "Second dashboard block header"
    And I set the field "Content" to "Second dashboard block content"
    And I press "Save changes"
    And I set the following administration settings values:
      | defaulthomepage | Totara dashboard |
    And I log out
    When I log in as "student1"
    Then I should see "My first dashboard" in the "#page-header" "css_element"
    And I should see "First dashboard block header"
    And I click on "My second dashboard" "link"
    And I should see "Second dashboard block header"
    And I click on "My learning" "link"
    And I should see "Course overview" in the "#region-main" "css_element"
    And I click on "Site home" "link"
    And I should see "I'm a label on the frontpage"
