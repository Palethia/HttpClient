<?php

declare(strict_types=1);

namespace Palethia\network\http\enum;

enum MimeType: int
{
	// TEXT
	case PLAIN = 1;
	case HTML = 2;
	case CSS = 3;
		// there is js&xml text type which should be 4&5
		// but there is application js&xml
		// so there is no need to add them
	case CSV = 6;
	case MD = 7;

		// APPLICATION
	case JSON = 8;
	case XML = 9;
	case XHTML = 10;
	case PDF = 11;
	case ZIP = 12;
	case GZIP = 13;
	case BINARY = 14;
	case FORM_DATA_NFU = 15;
	case SWF = 16;
	case WAM = 17;

		// IMAGE
	case JPEG = 18;
	case PNG = 19;
	case GIF = 20;
	case WEBP = 21;
	case SVG = 22;
	case TIFF = 23;
	case BMP = 24;

		// AUDIO
	case MP3 = 25;
	case WAV = 26;
	case OGG_AUD = 27;
	case AAC = 28;

		// FONT
	case WOFF = 29;
	case WOFF2 = 30;
	case TTF = 31;
	case OTF = 32;

		// MULTIPART
	case FORM_DATA_FU = 33;
	case ALTERNATIVE = 34;
	case MIXED = 35;

		// APPLICATION
	case TAR = 36;
	case RAR = 37;
	case SEVEN_ZIP = 38;

		// VIDEO
	case MP4 = 39;
	case WEBM = 40;
	case OGG_VID = 41;
	case AVI = 42;

	public function toString()
	{
		return match ($this) {
			// TEXT
			self::HTML => "text/html",
			self::CSS => "text/css",
			self::CSV => "text/csv",
			self::MD => "text/markdown",

			// APPLICATION
			self::JSON => "application/json",
			self::XML => "application/xml",
			self::XHTML => "application/xhtml+xml",
			self::PDF => "application/pdf",
			self::ZIP => "application/zip",
			self::GZIP => "application/gzip",
			self::BINARY => "application/octet-stream",
			self::FORM_DATA_NFU => "application/x-www-form-urlencoded",
			self::SWF => "application/x-shockwave-flash",
			self::WAM => "application/manifest+json",

			// IMAGE
			self::JPEG => "image/jpeg",
			self::PNG => "image/png",
			self::GIF => "image/gif",
			self::WEBP => "image/webp",
			self::SVG => "image/svg+xml",
			self::TIFF => "image/tiff",
			self::BMP => "image/bmp",

			// AUDIO
			self::MP3 => "audio/mpeg",
			self::WAV => "audio/wav",
			self::OGG_AUD => "audio/ogg",
			self::AAC => "audio/aac",

			// FONTS
			self::WOFF => "font/woff",
			self::WOFF2 => "font/woff2",
			self::TTF => "font/ttf",
			self::OTF => "font/otf",

			// MULTIPART
			self::FORM_DATA_FU => "multipart/form-data",
			self::ALTERNATIVE => "multipart/alternative",
			self::MIXED => "multipart/mixed",

			// APPLICATION
			self::TAR => "application/x-tar",
			self::RAR => "application/x-rar-compressed",
			self::SEVEN_ZIP => "x-7z-compressed",

			// VIDEO
			self::MP4 => "video/mp4",
			self::WEBM => "video/webm",
			self::OGG_VID => "video/ogg",
			self::AVI => "video/avi"
		};
	}
}
