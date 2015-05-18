@totara @totara_reportbuilder
Feature: Test that report builder reports can be scheduled
  Create a report
  Go to My Reports
  Create a schedule
  Check that it shows the scheduled report in the list

  Background: Set up a schedulable report
    Given I am on a totara site
    And I log in as "admin"
    And I navigate to "Manage reports" node in "Site administration > Reports > Report builder"
    And I set the field "Report Name" to "Schedulable Report"
    And I set the field "Source" to "User"
    And I press "Create report"

  @javascript
  Scenario: Report builder reports can be scheduled daily
    When I click on "My Reports" in the totara menu
    And I press "Add scheduled report"
    Then I should see "Schedulable Report"
    When I set the field "schedulegroup[frequency]" to "Daily"
    Then the "schedulegroup[daily]" "select" should be enabled
    And the "schedulegroup[weekly]" "select" should be disabled
    And the "schedulegroup[monthly]" "select" should be disabled
    And the "schedulegroup[hourly]" "select" should be disabled
    And the "schedulegroup[minutely]" "select" should be disabled
    And I set the field "schedulegroup[daily]" to "03:00"
    When I press "Save changes"
    Then I should see "Daily at 03:00 AM"

  @javascript
  Scenario: Report builder reports can be scheduled weekly
    When I click on "My Reports" in the totara menu
    And I press "Add scheduled report"
    Then I should see "Schedulable Report"
    When I set the field "schedulegroup[frequency]" to "Weekly"
    Then the "schedulegroup[daily]" "select" should be disabled
    And the "schedulegroup[weekly]" "select" should be enabled
    And the "schedulegroup[monthly]" "select" should be disabled
    And the "schedulegroup[hourly]" "select" should be disabled
    And the "schedulegroup[minutely]" "select" should be disabled
    And I set the field "schedulegroup[weekly]" to "Tuesday"
    When I press "Save changes"
    Then I should see "Weekly on Tuesday"

  @javascript
  Scenario: Report builder reports can be scheduled monthly
    When I click on "My Reports" in the totara menu
    And I press "Add scheduled report"
    Then I should see "Schedulable Report"
    When I set the field "schedulegroup[frequency]" to "Monthly"
    Then the "schedulegroup[daily]" "select" should be disabled
    And the "schedulegroup[weekly]" "select" should be disabled
    And the "schedulegroup[monthly]" "select" should be enabled
    And the "schedulegroup[hourly]" "select" should be disabled
    And the "schedulegroup[minutely]" "select" should be disabled
    And I set the field "schedulegroup[monthly]" to "7"
    When I press "Save changes"
    Then I should see "Monthly on the 7th"

  @javascript
  Scenario: Report builder reports can be scheduled hourly
    When I click on "My Reports" in the totara menu
    And I press "Add scheduled report"
    Then I should see "Schedulable Report"
    When I set the field "schedulegroup[frequency]" to "Every X hours"
    Then the "schedulegroup[daily]" "select" should be disabled
    And the "schedulegroup[weekly]" "select" should be disabled
    And the "schedulegroup[monthly]" "select" should be disabled
    And the "schedulegroup[hourly]" "select" should be enabled
    And the "schedulegroup[minutely]" "select" should be disabled
    And I set the field "schedulegroup[hourly]" to "6"
    When I press "Save changes"
    Then I should see "Every 6 hour(s) from midnight"

  @javascript
  Scenario: Report builder reports can be scheduled minutely
    When I click on "My Reports" in the totara menu
    And I press "Add scheduled report"
    Then I should see "Schedulable Report"
    When I set the field "schedulegroup[frequency]" to "Every X minutes"
    Then the "schedulegroup[daily]" "select" should be disabled
    And the "schedulegroup[weekly]" "select" should be disabled
    And the "schedulegroup[monthly]" "select" should be disabled
    And the "schedulegroup[hourly]" "select" should be disabled
    And the "schedulegroup[minutely]" "select" should be enabled
    And I set the field "schedulegroup[minutely]" to "15"
    When I press "Save changes"
    Then I should see "Every 15 minute(s) from the start of the hour"
