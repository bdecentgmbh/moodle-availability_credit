@availability @availability_credit
Feature: availability_credit
  In order to control student access to activities
  As a teacher
  I need to set credit conditions which prevent student access

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format | enablecompletion |
      | Course 1 | C1        | topics | 1                |
    And the following "users" exist:
      | username |
      | teacher1 |
      | student1 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following config values are set as admin:
      | enableavailability  | 1 |
    And the following "activities" exist:
      | activity | course | name   | Description | Page content | section |
      | page     | C1     | Page 1 | Test        | Test         |  1      |

  @javascript
  Scenario: Restrict based on credit amount
    # Basic setup.
    Given I log in as "teacher1"
    And I am on site homepage
    And I follow "Course 1"
    And I turn editing mode on

    # Add a Page with a date condition that does match (from the past).
    And I am on the "Page 1" "page activity" page
    And I navigate to "Settings" in current page administration
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Course credit payment" "button" in the "Add restriction..." "dialogue"
    And I set the field "cost" to "123"
    And I press "Save and return to course"

    # Log back in as student.
    When I log out
    And I log in as "student1"
    And I am on site homepage
    And I follow "Course 1"

    # Page 1 should appear with availability info.
    Then I should see "Page 1" in the "#section-1" "css_element"
    And I should see "you use your"
    And I should see "course credits"
