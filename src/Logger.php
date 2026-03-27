<?php

/**
 * Copyright © Dazoot Software S.R.L. All rights reserved.
 *
 * @website https://www.newsman.ro/
 *
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Newsman;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger as MonologLogger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Logger implements LoggerInterface
{
    private const LEVEL_MAP = [
        LogLevel::EMERGENCY => 600,
        LogLevel::ALERT => 550,
        LogLevel::CRITICAL => 500,
        LogLevel::ERROR => 400,
        LogLevel::WARNING => 300,
        LogLevel::NOTICE => 250,
        LogLevel::INFO => 200,
        LogLevel::DEBUG => 100,
    ];

    private MonologLogger $monolog;

    public function __construct(Config $config)
    {
        $handlers = [];
        $severity = $config->getLogSeverity();

        if ($severity !== Config::LOG_NONE) {
            $handler = new RotatingFileHandler(
                _PS_ROOT_DIR_ . '/var/logs/newsman.log',
                $config->getLogCleanDays(),
                $severity,
                true,
                null,
                true
            );
            $handler->setFilenameFormat('{filename}_{date}', 'Y-m-d');
            $handler->setFormatter(new LineFormatter(
                "[%datetime%] %level_name%: %message% %context% %extra%\n",
                'Y-m-d H:i:s',
                false,
                true
            ));
            $handlers[] = $handler;
        }

        $this->monolog = new MonologLogger('newsman', $handlers, [
            new PsrLogMessageProcessor(),
        ]);
    }

    public function emergency(string|\Stringable $message, array $context = []): void
    {
        $this->monolog->emergency($message, $context);
    }

    public function alert(string|\Stringable $message, array $context = []): void
    {
        $this->monolog->alert($message, $context);
    }

    public function critical(string|\Stringable $message, array $context = []): void
    {
        $this->monolog->critical($message, $context);
    }

    public function error(string|\Stringable $message, array $context = []): void
    {
        $this->monolog->error($message, $context);
    }

    public function warning(string|\Stringable $message, array $context = []): void
    {
        $this->monolog->warning($message, $context);
    }

    public function notice(string|\Stringable $message, array $context = []): void
    {
        $this->monolog->notice($message, $context);
    }

    public function info(string|\Stringable $message, array $context = []): void
    {
        $this->monolog->info($message, $context);
    }

    public function debug(string|\Stringable $message, array $context = []): void
    {
        $this->monolog->debug($message, $context);
    }

    /**
     * PSR-3 generic log method.
     *
     * @param mixed $level A PSR-3 LogLevel constant string
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $intLevel = self::LEVEL_MAP[$level] ?? null;

        if (null === $intLevel) {
            throw new \Psr\Log\InvalidArgumentException(sprintf('Unknown log level: %s', $level));
        }

        $this->monolog->log($intLevel, $message, $context);
    }

    public function logException(\Throwable $e): void
    {
        $this->error($e->getMessage(), ['exception' => $e]);
    }
}
