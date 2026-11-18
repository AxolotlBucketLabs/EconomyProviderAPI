<?php

/*
 * EconomyProviderAPI - Simple and unified economy API for PocketMine-MP
 *
 * Licensed under the Apache License, Version 2.0
 * https://www.apache.org/licenses/LICENSE-2.0
 */

declare(strict_types=1);

namespace AxolotlBucketLabs\EconomyProviderAPI\exception;

use RuntimeException;

/**
 * Thrown when an unregistered provider name is requested.
 *
 * This means the provider name you passed to EconomyProviderAPI::getProvider()
 * is not recognized. Check spelling or register a custom provider first.
 */
class UnknownProviderException extends RuntimeException {
}
