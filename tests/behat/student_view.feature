@local @local_feedbackviewer
Feature: Show my feedback responses in a course
  In order to review my feedback responses in a course
  As a student
  I need to see all my feedback

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Terry | Teacher | teacher1@example.com |
      | student1 | Sally | Student | student1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And I log in as "admin"
    And I navigate to "Manage activities" node in "Site administration > Plugins > Activity modules"
    And I click on "Show" "link" in the "Feedback" "table_row"
    And I log out

  @javascript
  Scenario: View a student's feedback
    When I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Feedback" to section "1" and I fill the form with:
      | Name                | Football |
      | Description         | None |
      | Record user names   | User's name will be logged and shown with answers |
    And I follow "Football"
    And I follow "Edit questions"
    And I set the field "id_typ" to "Longer text answer"
    And I set the following fields to these values:
      | Question | Favorite football team? |
    And I press "Save question"
    And I follow "Course 1"
    And I add a "Feedback" to section "2" and I fill the form with:
      | Name                | Transportation |
      | Description         | None |
      | Record user names   | User's name will be logged and shown with answers |
    And I follow "Transportation"
    And I follow "Edit questions"
    And I set the field "id_typ" to "Longer text answer"
    And I set the following fields to these values:
      | Question | Favorite transport mode? |
    And I press "Save question"
    And I log out

    And I log in as "student1"
    And I follow "Course 1"
    And I navigate to "My feedback" node in "Course administration > Feedback viewer"
    And I should see "Not completed yet"
    And I follow "Course 1" 
    And I follow "Football"
    And I follow "Answer the questions"
    And I set the field "Favorite football team?" to "Michigan"
    And I press "Submit your answers"
    And I press "Continue"
    And I follow "Transportation"
    And I follow "Answer the questions"
    And I set the field "Favorite transport mode?" to "Rail"
    And I press "Submit your answers"
    And I log out

    And I log in as "student1"
    And I follow "Course 1"
    And I navigate to "My feedback" node in "Course administration > Feedback viewer"
    Then I should see "Michigan"
    And I should see "Rail"
    And I log out
