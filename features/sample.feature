Feature: Testing sample REST services
    In order to maintain user information through the services
    as a service user
    I want to see if the services work as expected 

    Scenario: Creating a New Employee
        Given that I want to add a new employee
            And that employee ID of the employee is "007"
            And that name of the employee is "James Bond"
            And that age of the employee is "27"
        When I request "/employee"
        Then the response status code should be 200
            And the response is JSON
            And the response should be "true"

    Scenario: Finding an Existing Employee
        Given that I want to find an employee
        When I request "/employee/007"
        Then the response status code should be 200
            And the response is JSON
            And in the response name of the employee is "James Bond"
            And in the response age of the employee is "27"
            And in the response there is no field called "gender"

    Scenario: Updating an Existing Employee
        Given that I want to update an employee
        And that employee ID of the employee is "007"
        And I'm changing age of the employee to "38"
        When I request "/employee"
        Then the response status code should be 200
            And the response is JSON
            And the response should be "true"

    Scenario: Deleting Existing and Non-existing Employees
        Given that I want to delete an employee
            And I request "/employee/007"
        Then the response status code should be 200
            And the response is JSON
            And the response should be "true"
        Given that I want to delete an employee
            And I request "/employee/008"
        Then the response status code should be 400
            And the response is not JSON
            And the response should be "Unable to delete because the employee does not exist."
