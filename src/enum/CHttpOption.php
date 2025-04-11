<?php

declare(strict_types=1);

namespace Palethia\network\http\enum;

final class CHttpOption
{
	public const POST_FIELDS = "__POST_FEILDS__";
	public const GET_QUERY = "__GET_QUERY__";

	// case STARTING_POINT = 0xffffff;
	// case POST_FIELDS = self::STARTING_POINT->value + 1;
	// case END_POINT = self::POST_FIELDS->value + 1;
}
