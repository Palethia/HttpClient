<?php

declare(strict_types=1);

namespace Palethia\network\http;

use Palethia\network\http\enum\HttpStatusCode;

class HttpResponse
{
	public function __construct(
		public readonly string|bool $data,
		public readonly HttpStatusCode $http_code
	) {
		// NOOP
	}
}
