<?php

declare(strict_types=1);

namespace Palethia\network\http\enum;

enum HttpMethod: int
{
	case GET = 1;
	case POST = 2;

	protected function equals(self $method): bool
	{
		return $this === $method;
	}

	public function isGet(): bool
	{
		return $this->equals(self::GET);
	}

	public function isPost(): bool
	{
		return $this->equals(self::POST);
	}
}
