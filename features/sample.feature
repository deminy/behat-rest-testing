Feature: Testing sample REST services
    In order to maintain user information through the services
    as a service user
    I want to see if the services work as expected 

    Scenario: Creating a New Employee
        When I send a POST request to "/employee" with values:
            | employeeId | 7          |
            | name       | James Bond |
            | age        | 27         |
            # Next step will add "Accept-Charset: utf-8" in HTTP header when making the API call. The header doesn't
            # have any actual effect on the APIs nor the tests; we have it included/listed here just for demonstration,
            # in case you need to know how to add HTTP headers when testing API calls.
            And I set header "Accept-Charset" with value "utf-8"
        Then response code should be 200
            And the response should be "true"

    Scenario: Finding an Existing Employee
        When I send a GET request to "/employee/7"
        Then response code should be 200
            And the response should contain json:
            """"
            {
                "name": "James Bond",
                "age": "27"
            }
            """"
        And in the response there is no field called "gender"

    Scenario: Updating an Existing Employee
        When I send a PUT request to "/employee/7" with values:
            | age | 38 |
        Then response code should be 200
            And the response should be "true"

    Scenario: Deleting Existing and Non-existing Employees
        Given I send a DELETE request to "/employee/7"
        Then response code should be 200
            And the response should be "true"
        Given I send a DELETE request to "/employee/8"
        Then response code should be 400
            And the response should contain "Unable to delete because the employee does not exist."
