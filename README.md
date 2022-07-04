# Primarybyte Backend Interview

### Description:

There is an API which relies on 3rd party service and designed to verify emails.
Currently, there are 3 endpoints available:
- POST /email-verification/

  {"email": "some@email.here"}

  Returns:
    - 200 {"success": true, "id": 123} - succesfully added
    - 400 - no email provided
    - 422 - bad email provided
  
  Runs email verification on given email.


- GET /email-verification/:email

  Returns:
    - 200 - Email details with last verification info
    - 202 - Email details with no last verification info
    - 404 - Email not found
    
  Returns email details and last verification info if available


- DELETE /email-verification/:email

  Returns:
    - 200 - Email removed successfully
    - 404 - Email not found

### Tasks

1) Currently, GET request always returns 200 response for existing emails. 
Please, update the code to return 202 response when no verification info available.
2) Currently, POST request always runs email verification in-place. 
Please, update the code to queue the verification using Symfony Messenger component instead. 
Use existing `EmailMessage` class.
3) Implement message handler for `EmailMessage` class. Add unit test for the handler.
4) Implement CachedEmailVerificationClient - use cache for requests, set TTL to 1 week.

Note: some tests are failed right now. You need to update the code to make tests working. **Please, do not modify tests.**

### Useful information:

Fully working environment can be run using docker:
```docker-compose up```

There are 2 docker containers with PHP - one for API and another one for Messenger consumer.

**Watchout:** Don't forget to restart consumer container after modifying the code.

Tests can be run inside docker container by composer script: ```composer run-script run-tests```

You can use `postman.json` for testing API in Postman
