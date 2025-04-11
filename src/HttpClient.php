<?php

declare(strict_types=1);

namespace Palethia\network\http;

use Closure;
use CurlHandle;
use Palethia\network\http\enum\CHttpOption;
use Palethia\network\http\enum\HttpMethod;
use Palethia\network\http\enum\HttpStatusCode;
use RuntimeException;

class HttpClient
{
	public const DEFAULT_USER_AGENT = "Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.6533.103 Mobile Safari/537.36";

	private CurlHandle $ch;

	/**
	 * HttpClient construct.
	 * 
	 * @param array $default_options
	 */
	public function __construct(
		private array $default_options = [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => self::DEFAULT_USER_AGENT,
			CURLOPT_SSL_OPTIONS => CURLSSLOPT_NATIVE_CA
		]
	) {
		$this->ch = curl_init();
	}

	public function __destruct()
	{
		$this->close();
	}

	private function l98d_in_array(string|int $item, array $arr): bool
	{
		if (PHP_OS !== "Linux") {
			return in_array($item, $arr);
		}
		return preg_match('/\b(' . implode("|", array_map(fn($word) => preg_quote($word, "/"), $arr)) . ')\b/i', $item) === 1;
	}

	private function internalThrowError(string $message, bool $silent): bool
	{
		if ($silent) {
			return false;
		} else {
			throw new RuntimeException($message);
		}
	}

	/**
	 * performs a normal http request.
	 *
	 * when you are going to json_encode the "options" for the json data
	 * you might be required to put JSON_UNESCAPED_SLASHES depending on the website.
	 * so that for example -- is this correct i dont remember
	 * {
	 *   "abc": "/test" 
	 * }
	 * is whats required but php turns it to "\/" from "/"
	 *
	 * so that field prevents it.
	 *
	 * never use `ignore_status` it's internal.
	 *
	 * @param HttpMethod $method
	 * @param string $url
	 * @param mixed $options
	 * @param array $header  fuck its attached to my gitlab
	 * @param boolean $silent
	 * @param boolean $ignore_status
	 * 
	 * @return boolean|HttpResponse
	 */
	public function request(HttpMethod $method, string $url, array $options = [], array $header = [], bool $correct_header = false, bool $silent = false, bool $ignore_status = false): bool|HttpResponse
	{
		$lc_header = array_map(function ($value) {
			if (!is_string($value)) {
				throw new RuntimeException("Invalid header element with key '$value' given in request.");
			}
			return strtolower($value);
		}, array_keys($header));

		if ($method->isPost()) {
			if ($this->l98d_in_array("user-agent", $lc_header) && isset($options[CURLOPT_USERAGENT])) {
				unset($options[CURLOPT_USERAGENT]);
			} else if (count(array_diff($this->default_options, $options)) !== count($this->default_options)) {
				return $this->internalThrowError("Invalid options given in request. You cannot override default options", $silent);
			}
		}

		if ($correct_header) {
			$corrected_header = [];
			foreach ($header as $key => $value) {
				$corrected_header[] = sprintf("%s: %s", $key, $value);
			}
		}

		$request_options = [
			CURLOPT_URL => $url,
			CURLOPT_HTTPHEADER => $correct_header ? $corrected_header : $header
		];

		if ($method->isPost()) {
			$request_options[CURLOPT_POST] = true;
			$request_options[CURLOPT_POSTFIELDS] = $options[CHttpOption::POST_FIELDS] ?? "";
			unset($options[CHttpOption::POST_FIELDS]);
		} elseif ($method->isGet()) {
			$request_options[CURLOPT_URL] = $url . "?" . http_build_query($options[CHttpOption::GET_QUERY] ?? []);
			unset($options[CHttpOption::GET_QUERY]);
		}

		if (!empty($options)) {
			$request_options = $request_options + $options;
		}

		curl_setopt_array($this->ch, $request_options + $this->default_options);

		$data = curl_exec($this->ch);

		if (!$ignore_status && (!$data || curl_errno($this->ch))) {
			return $this->internalThrowError("Unable to curl (" . curl_getinfo($this->ch, CURLINFO_HTTP_CODE) . "): " . curl_error($this->ch), $silent);
		}

		return new HttpResponse(
			$data,
			HttpStatusCode::from(curl_getinfo($this->ch, CURLINFO_HTTP_CODE))
		);
	}

	/**
	 * performs the normal http request but will request again  pfor a specific amount of time
	 * if the status code is the same as the one specified in the function.
	 *
	 * also check the "request" function for any info related to the other fields that was left out.
	 *
	 * @param HttpMethod $method
	 * @param string $url
	 * @param HttpStatusCode $status_code
	 * @param int $num_of_retries
	 * @param null|Closure $retry_closure(&$http_method, &$url, &$options, &$header, &$correct_header, $retry_number, &$silent) 
	 * @param mixed $options
	 * @param array $header
	 * @param boolean $silent
	 *
	 * @return boolean|HttpResponse
	 */
	public function requestRepeated(HttpMethod $method, string $url, HttpStatusCode $status_code = HttpStatusCode::FORBIDDEN, int $num_of_retries = 8, ?Closure $retry_closure = null, array $options = [], array $header = [], bool $correct_header = false, bool $silent = false): bool|HttpResponse
	{
		$perform_request_fn = fn(HttpMethod $m, string $u, array $o, array $h, bool $ch, bool $s) => $this->request($m, $u, $o, $h, $ch, $s, true);
		$is_invalid_data_fn = fn(HttpResponse $response) => is_bool($response->data) || $response->http_code->value === $status_code->value;
		$counter = 0;
		do {
			if ($counter > 1) {
				$this->reset();
			}
			++$counter;
			if ($retry_closure !== null) {
				$retry_closure($method, $url, $options, $header, $correct_header, $counter, $silent);
			}
			$response = $perform_request_fn($method, $url, $options, $header, $correct_header, $silent);
		} while ($is_invalid_data_fn($response) && $counter < $num_of_retries);
		if ($is_invalid_data_fn($response)) {
			return $this->internalThrowError("Unable to curl (" . $response->http_code->value . "): " . curl_error($this->ch), $silent);
		}
		return $response;
	}

	/**
	 * Extracts a header value.
	 * 
	 * @param string $name
	 * 
	 * @return string
	 */
	public function extractHeaderValue(string $name): string
	{
		return curl_getinfo($this->ch, CURLINFO_HEADER_OUT)[$name] ?? "";
	}

	/**
	 * Resets the curl session options.
	 * 
	 * @return void
	 */
	public function reset(): void
	{
		curl_reset($this->ch);
	}

	/**
	 * Closes current curl session and creates a new one in it's place.
	 * 
	 * @return void
	 */
	public function renew(): void
	{
		$this->close();
		$this->ch = curl_init();
	}

	/**
	 * Closes the curl session.
	 * 
	 * @return void
	 */
	public function close(): void
	{
		curl_close($this->ch);
	}
}
