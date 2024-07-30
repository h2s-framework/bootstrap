<?php

namespace Siarko\Bootstrap\Api;

interface AppInterface
{

    /**
     * Start application
     *
     * @return void
     */
    public function start(): void;

    /**
     * Run sanity checks to ensure that application is properly configured
     *
     * @return void
     */
    public function runSanityChecks(): void;

    /**
     * Handle errors
     *
     * @param \Throwable $exception
     * @return void
     */
    public function handleErrors(\Throwable $exception): void;

}