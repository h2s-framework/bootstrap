<?php

namespace Siarko\Bootstrap;

use Psr\Log\LoggerInterface;
use Siarko\Bootstrap\Api\AppInterface;
use Siarko\Bootstrap\Exception\PhpErrorException;
use Siarko\DependencyManager\Api\Config\ConfiguratorInterface;
use Siarko\DependencyManager\Config\Init\Configurator;
use Siarko\DependencyManager\DependencyManager;

class Bootstrap
{

    private DependencyManager $dependencyManager;
    private ?AppInterface $app = null;
    private ?LoggerInterface $logger = null;

    /**
     * @param string $projectRoot
     * @param ConfiguratorInterface|null $configurator
     */
    public function __construct(
        string                 $projectRoot,
        private readonly ?ConfiguratorInterface $configurator = null
    ) {
        $this->configure($projectRoot);
    }

    /**
     * Run application
     * @param string $appType
     * @return void
     */
    public function run(string $appType): void
    {
        try {
            $this->logger = $this->dependencyManager->get(LoggerInterface::class);
            $this->app = $this->dependencyManager->get($appType);
            $this->app->runSanityChecks();
            $this->app->start();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Configure dependency manager
     *
     * @param string $projectRoot
     * @return void
     */
    private function configure(string $projectRoot): void
    {
        $this->registerErrorHandler();
        try {
            $this->dependencyManager = new DependencyManager();
            ($this->configurator ?? new Configurator())->configure($this->dependencyManager, $projectRoot);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * @param \Throwable $exception
     * @return void
     */
    private function handleException(\Throwable $exception): void
    {
        if ($this->app) {
            try{
                $this->app->handleErrors($exception);
            }catch (\Exception $e){
                $this->nativeHandleError($e);
            }
            return;
        }
        $this->nativeHandleError($exception);
        die();
    }

    /**
     * Handle exception using native PHP error handling
     *
     * @param \Throwable $exception
     * @return void
     */
    private function nativeHandleError(\Throwable $exception): void
    {
        $this->logger?->error($exception->getMessage(), ['exception' => $exception]);

        echo "<pre>[Bootstrap] Application error: ". get_class($exception) .': ' . $exception->getMessage() . "\nTrace:\n";
        echo $exception->getTraceAsString();
        echo "</pre>";
        if ($exception = $exception->getPrevious()) {
            $this->nativeHandleError($exception);
        };
    }

    /**
     * @return void
     */
    private function registerErrorHandler(): void
    {
        register_shutdown_function(function() {
            $errorData = error_get_last();
            if(empty($errorData)){
                return;
            }
            $this->handleException(new PhpErrorException(
                message: $errorData['message'],
                code: $errorData['type'],
                file: $errorData['file'],
                line: $errorData['line']
            ));
        });
    }

}