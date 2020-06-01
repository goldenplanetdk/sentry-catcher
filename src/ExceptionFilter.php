<?php

namespace Gpp\Sentry;

use Doctrine\DBAL\Exception\DriverException;
use Sentry\Event;

class ExceptionFilter {

	private const FILTER_EXCEPTION_BY_MESSAGE = [
		\ErrorException::class => [
			'Error while sending STMT_PREPARE packet.',
			'MySQL server has gone away',
		],
		\AMQPException::class => [
			'Library error: a socket error occurred',
		],
		DriverException::class => [
			'MySQL server has gone away',
		]
	];

	public function __invoke(Event $event): ?Event {
		$exceptions = $event->getExceptions();
		foreach ($exceptions as $exception) {
			$type = $exception['type'] ?? null;
			$messages = $this->getExceptionByMessages()[$type] ?? [];
			$exceptionMessage = $exception['value'] ?? '';
			foreach ($messages as $message) {
				if (strpos($exceptionMessage, $message) !== false) {
					return null;
				}
			}
		}

		return $event;
	}

	protected function getExceptionByMessages(): array {
		return self::FILTER_EXCEPTION_BY_MESSAGE;
	}
}