@javascript @totara @totara_dashboard
Feature: Perform basic dashboard user changes
  In order to ensure that dashboard work as expected
  As a user
  I need to change dashboards

  Background:
  Given I am on a totara site
    And the following "users" exist:
      | username |
      | learner1 |
      | learner2 |
  And the following "cohorts" exist:
      | name | idnumber |
      | Cohort 1 | CH1 |
  And the following totara_dashboards exist:
    | name | locked | published | cohorts |
    | First dashboard | 1 | 1 | CH1 |
    | Dashboard locked published | 1 | 1 | CH1 |
    | Dashboard unlocked published | 0 | 1 | CH1 |
    | Dashboard unpublished | 1 | 0 | CH1 |
    | Dashboard unassigned | 1 | 1 | |
  And I log in as "admin"
  And I set the following administration settings values:
    | defaulthomepage | Totara dashboard |
  And I add "learner1" user to "CH1" cohort members
  And I log out

  Scenario: Add block to personal version of second dashboard and then reset
    When I log in as "admin"
    And I add "learner2" user to "CH1" cohort members
    And I log out
    And I log in as "learner1"
    And I follow "Home"
    And I follow "Dashboard unlocked published"

    # Add block.
    When I press "Customize dashboard"
    And I add the "Latest news" block
    Then "Latest news" "block" should exist
    And I press "Stop customizing this dashboard"
    And "Latest news" "block" should exist
    And I log out

    # Check that other users unaffected.
    When I log in as "learner2"
    And I follow "Dashboard unlocked published"
    Then "Latest news" "block" should not exist
    And I log out

    # Reset dashboard to master version.
    When I log in as "learner1"
    And I follow "Dashboard unlocked published"
    And "Latest news" "block" should exist
    And "Customize dashboard" "button" should exist
    And I press "Customize dashboard"
    And I press "Reset dashboard to default"
    Then "Latest news" "block" should not exist

  Scenario: Cannot change locked dashboard
    When I log in as "learner1"
    And I follow "Home"
    And I follow "Dashboard locked published"
    Then "Customize dashboard" "button" should not exist

  Scenario: Cannot see dashboard that is unpublished/unassigned
    And I log in as "learner1"
    When I follow "Home"
    Then I should not see "Dashboard unassigned"
    And I should not see "Dashboard unpublished"
    And I should see "Dashboard locked published"
