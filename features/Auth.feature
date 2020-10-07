Feature:
    Scenario: Call a not found route
        When I add "Content-Type" header equal to "application/json"
        And I send a "GET" request to "/api/v1/not-found-route"
        Then the response status code should be 404

    Scenario: Try to register a user with missing "lastName" field
        When I add "Content-Type" header equal to "application/json"
        And I send a "POST" request to "/api/v1/register" with body:
        """
        {
            "firstName": "jhon",
            "email": "jhon.doe@gmail.com",
            "password": "test-pass",
            "checkPassword": "test-pass",
            "phone": "123456"
        }
        """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON node "message" should be equal to the string "lastName: This value should not be blank."

    Scenario: Successfully register a new user
        When I add "Content-Type" header equal to "application/json"
        And I send a "POST" request to "/api/v1/register" with body:
        """
        {
            "firstName": "jhon",
            "lastName": "doe",
            "email": "jhon.doe@gmail.com",
            "password": "test-pass",
            "checkPassword": "test-pass",
            "phone": "123456"
        }
        """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON node "id" should not be null
        And the JSON node "email" should be equal to the string "jhon.doe@gmail.com"
        And the JSON node "token" should not be null
        ### The following is not recommended, only to see our FeatureContext in work
        And the JSON node "id" should be greater than the number 0
        And the JSON node "firstName" length should be 4

    Scenario: Dump the response (To debug for example - Here the email is already used)
        When I add "Content-Type" header equal to "application/json"
        And I send a "POST" request to "/api/v1/register" with body:
        """
        {
            "firstName": "jhon",
            "lastName": "doe",
            "email": "jhon.doe@gmail.com",
            "password": "test-pass",
            "checkPassword": "test-pass",
            "phone": "123456"
        }
        """
        Then dump the response

