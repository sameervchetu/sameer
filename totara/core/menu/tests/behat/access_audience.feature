@totara @totara_menu @javascript
Feature: Test restricting Totara custom menu access by audience
  In order to limit access to menu items
  As a user
  I need to restrict by audience

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email             |
      | user1    | user      | 1        | user1@example.com |
      | user2    | user      | 2        | user2@example.com |
      | user3    | user      | 3        | user3@example.com |
    And the following "cohorts" exist:
      | name | idnumber |
      | aud1 | aud1     |
      | aud2 | aud2     |
    And I log in as "admin"

  Scenario: Test menu access with one audience
    Given I add "user 1 (user1@example.com)" user to "aud1" cohort members
    And I navigate to "Main menu" node in "Site administration > Appearance"
    And I click on "Add new menu item" "button"
    And I set the following fields to these values:
      | Parent item              | Top                     |
      | Menu title               | test item               |
      | Visibility               | Use custom access rules |
      | Menu default url address | /my/                    |
    And I click on "Add new menu item" "button"
    And I click on "Access" "link"
    And I expand all fieldsets
    And I click on "Restrict access by audience" "text" in the "#fitem_id_audience_enable" "css_element"
    And I set the following fields to these values:
      | Audience aggregation | Any |
    And I click on "Add audiences" "button"
    And I click on "aud1" "text" in the "#course-cohorts-visible-dialog .treeview" "css_element"
    And I click on "OK" "button"
    And I wait "1" seconds
    And I click on "Save changes" "button"
    # Test user 1 can see the menu item.
    When I log out
    And I log in as "user1"
    Then I should see "test item" in the "#totaramenu" "css_element"
    # Test user 2 can not see the menu item.
    When I log out
    And I log in as "user2"
    Then I should not see "test item" in the "#totaramenu" "css_element"

  Scenario: Test menu access with multiple audiences and using any as the aggregation
    Given I add "user 1 (user1@example.com)" user to "aud1" cohort members
    And I add "user 3 (user3@example.com)" user to "aud2" cohort members
    And I navigate to "Main menu" node in "Site administration > Appearance"
    And I click on "Add new menu item" "button"
    And I set the following fields to these values:
      | Parent item              | Top                     |
      | Menu title               | test item               |
      | Visibility               | Use custom access rules |
      | Menu default url address | /my/                    |
    And I click on "Add new menu item" "button"
    And I click on "Access" "link"
    And I expand all fieldsets
    And I click on "Restrict access by audience" "text" in the "#fitem_id_audience_enable" "css_element"
    And I set the following fields to these values:
      | Audience aggregation | Any |
    And I click on "Add audiences" "button"
    And I click on "aud1" "text" in the "#course-cohorts-visible-dialog .treeview" "css_element"
    And I click on "aud2" "text" in the "#course-cohorts-visible-dialog .treeview" "css_element"
    And I click on "OK" "button"
    And I wait "1" seconds
    And I click on "Save changes" "button"
    # Test user 1 can see the menu item.
    When I log out
    And I log in as "user1"
    Then I should see "test item" in the "#totaramenu" "css_element"
    # Test user 2 can not see the menu item.
    When I log out
    And I log in as "user2"
    Then I should not see "test item" in the "#totaramenu" "css_element"
    # Test user 3 can see the menu item.
    When I log out
    And I log in as "user3"
    Then I should see "test item" in the "#totaramenu" "css_element"

  Scenario: Test menu access with multiple audiences and using all as the aggregation
    Given I add "user 1 (user1@example.com)" user to "aud1" cohort members
    And I add "user 1 (user1@example.com)" user to "aud2" cohort members
    And I add "user 2 (user2@example.com)" user to "aud2" cohort members
    And I navigate to "Main menu" node in "Site administration > Appearance"
    And I click on "Add new menu item" "button"
    And I set the following fields to these values:
      | Parent item              | Top                     |
      | Menu title               | test item               |
      | Visibility               | Use custom access rules |
      | Menu default url address | /my/                    |
    And I click on "Add new menu item" "button"
    And I click on "Access" "link"
    And I expand all fieldsets
    And I click on "Restrict access by audience" "text" in the "#fitem_id_audience_enable" "css_element"
    And I set the following fields to these values:
      | Audience aggregation | All |
    And I click on "Add audiences" "button"
    And I click on "aud1" "text" in the "#course-cohorts-visible-dialog .treeview" "css_element"
    And I click on "aud2" "text" in the "#course-cohorts-visible-dialog .treeview" "css_element"
    And I click on "OK" "button"
    And I wait "1" seconds
    And I click on "Save changes" "button"
    # Test user 1 can see the menu item.
    When I log out
    And I log in as "user1"
    Then I should see "test item" in the "#totaramenu" "css_element"
    # Test user 2 can see the menu item.
    When I log out
    And I log in as "user2"
    Then I should not see "test item" in the "#totaramenu" "css_element"
