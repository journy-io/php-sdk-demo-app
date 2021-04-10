<?php declare(strict_types=1);

namespace ShopManager\Users;

use InvalidArgumentException;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

final class PhoneNumber
{
    private \libphonenumber\PhoneNumber $parsed;

    public function __construct(string $number)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $parsed = $phoneUtil->parse($number, "BE");
        } catch (NumberParseException $e) {
            throw new InvalidArgumentException("Invalid phone: {$number}");
        }

        if (!$parsed) {
            throw new InvalidArgumentException("Invalid phone: {$number}");
        }

        if ($phoneUtil->isValidNumber($parsed) === false) {
            throw new InvalidArgumentException("Invalid phone: {$number}");
        }

        $this->parsed = $parsed;
    }

    public function __toString(): string
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        return $phoneUtil->format($this->parsed, PhoneNumberFormat::INTERNATIONAL);
    }
}
