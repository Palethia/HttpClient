# HttpClient
Palethia HttpClient is a lightweight PHP HTTP client designed for seamless integration with Palethia Proxy and other web applications. It provides an intuitive interface for sending HTTP requests using cURL with built-in retry logic and error handling.

---

## Installation & Import

Install via Composer:
```bash
composer require palethia/httpclient
```

After installation, include Composer's autoloader in your PHP script and import the needed classes:

```php
<?php
require 'vendor/autoload.php';

use Palethia\network\http\HttpClient;
use Palethia\network\http\enum\HttpMethod;
use Palethia\network\http\enum\CHttpOption;
```

This setup allows you to start making HTTP requests using the HttpClient right away.

---

## API Documentation & Usage

### Overview
**Palethia HttpClient** simplifies HTTP requests with:
- GET/POST support
- Retry logic for failed requests
- Customizable cURL options
- Query parameter and POST data helpers
- Error handling and silent mode

### Key Concepts
- **HTTP Methods**: Use `HttpMethod` enums (e.g., `HttpMethod::GET`).
- **CHttpOption**: Special options for query parameters (`CHttpOption::GET_QUERY`) and POST data (`CHttpOption::POST_FIELDS`).
- **cURL Options**: Merge custom options with defaults (user options override defaults).

---

### Methods

#### `__construct(array $default_options = [...])`
Initializes client with default cURL options:
```php
$client = new HttpClient();
```
**Defaults**:
- `CURLOPT_RETURNTRANSFER => true`
- `CURLOPT_USERAGENT => DEFAULT_USER_AGENT`
- `CURLOPT_SSL_OPTIONS => CURLSSLOPT_NATIVE_CA`

---

#### 2. `request(HttpMethod $method, string $url, array $options = [], array $header = [], bool $correct_header = false, bool $silent = false, bool $ignore_status = false)`
- Sends a single HTTP request.
- **Parameters:**
  - **`$method`**: The HTTP method to use (e.g., `HttpMethod::GET` or `HttpMethod::POST`).
  - **`$url`**: The target URL.
  - **`$options`**: An array of cURL options or custom options. For example, use `CHttpOption::GET_QUERY` to pass a query string for GET requests or `CHttpOption::POST_FIELDS` for the POST data.
  - **`$header`**: An associative array of headers (optionally corrected with `$correct_header`).
  - **`$correct_header`**: If true, the headers will be formatted as `"Key: Value"`.
  - **`$silent`**: If set to true, errors will be suppressed and the method will return `false` instead of throwing an exception.
  - **`$ignore_status`**: Primarily used internally; skips status code verification.
- **Returns:** An `HttpResponse` object on success, or `false` if an error occurs (in silent mode).
- **Example (Simple GET with Query Parameters):**
  ```php
  <?php

  declare(strict_types=1);

  namespace {

      use Palethia\network\http\enum\CHttpOption;
      use Palethia\network\http\enum\HttpMethod;
      use Palethia\network\http\HttpClient;

      require "vendor/autoload.php";

      $client = new HttpClient();
      $response = $client->request(
          HttpMethod::GET,
          "https://api.arabskills.net/status",
          [
              CURLOPT_FOLLOWLOCATION => true,
              CHttpOption::GET_QUERY => ['type' => 'json']
          ]
      );
      $client->close();
      var_dump($response->data);
  }
  ```
  In this example, `CHttpOption::GET_QUERY` is used to add the query parameter `type=json` to the GET request.

---

#### 3. `requestRepeated(HttpMethod $method, string $url, HttpStatusCode $status_code, int $num_of_retries = 8, ?Closure $retry_closure = null, array $options = [], array $header = [], bool $correct_header = false, bool $silent = false)`
- **Purpose:** Sends the HTTP request repeatedly until a successful response is received or the maximum number of retries is reached.
- **Parameters:**
  - **`$status_code`**: The HTTP status code which—if matched—triggers a retry.
  - **`$num_of_retries`**: Maximum attempts before giving up.
  - **`$retry_closure`**: An optional closure to manipulate the request parameters for each retry. This closure provides access to the current retry count.
- **Usage Example:**
  ```php
  <?php

  use Palethia\network\http\enum\HttpMethod;
  use Palethia\network\http\HttpClient;
  use Palethia\network\http\enum\HttpStatusCode;

  $client = new HttpClient();
  $response = $client->requestRepeated(
      HttpMethod::GET,
      "https://api.example.com/retry-endpoint",
      HttpStatusCode::FORBIDDEN,  // retries if a forbidden status is returned
      5,                          // maximum 5 retries
      function (&$method, &$url, &$options, &$header, &$correct_header, $retryNumber, &$silent) {
          // Optionally adjust your options on each retry
          $options[CURLOPT_TIMEOUT] = 5 * $retryNumber;
      }
  );
  $client->close();
  echo $response->data;
  ```
  This method is useful when dealing with unstable endpoints where temporary errors might occur.

---

#### 4. `extractHeaderValue(string $name): string`
- **Purpose:** Retrieves a specific header value that was sent out by the client.
- **Usage:**
  ```php
  $headerValue = $client->extractHeaderValue('Content-Type');
  ```
  This is useful for logging or for further processing based on the response headers.

---

#### 5. `reset()`, `renew()`, and `close()`
- **`reset()`**: Resets the current cURL session, returning it to its default state.
- **`renew()`**: Closes the current session and re-initializes a new one.
- **`close()`**: Closes the active cURL session.
- **Usage:**
  ```php
  $client->reset();  // Reset options
  $client->renew();  // Reinitialize cURL session
  $client->close();  // Close session when done
  ```

---

### Error Handling

- The `HttpClient` methods throw `RuntimeException` errors if an issue is encountered (unless the `$silent` parameter is true).
- Ensure you wrap your calls in `try-catch` blocks if you want to handle errors gracefully:
  ```php
  try {
      $response = $client->request(HttpMethod::GET, 'https://api.example.com/data', [
          CURLOPT_FOLLOWLOCATION => true,
          CHttpOption::GET_QUERY => ["type" => "json"]
      ]);
      $client->close();
      echo $response->data;
  } catch (\RuntimeException $e) {
      echo "Request failed: " . $e->getMessage();
  }
  ```
