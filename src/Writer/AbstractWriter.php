<?php

declare(strict_types=1);

namespace Laminas\Log\Writer;

use Laminas\Log\Exception;
use Laminas\Log\Filter;
use Laminas\Log\FilterPluginManager as LogFilterPluginManager;
use Laminas\Log\Formatter;
use Laminas\Log\FormatterPluginManager as LogFormatterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ErrorHandler;
use Traversable;
use function is_array;
use function is_int;
use function is_string;
use function iterator_to_array;
use function sprintf;
use const E_WARNING;

/**
 * @todo Remove aliases for parent namespace's FilterPluginManager and
 *    FormatterPluginManager once the deprecated versions in the current
 *    namespace are removed (likely v3.0).
 */
abstract class AbstractWriter implements WriterInterface
{
    /**
     * Filter plugins
     *
     * @var LogFilterPluginManager|null
     */
    protected ?LogFilterPluginManager $filterPlugins = null;

    /**
     * Formatter plugins
     *
     * @var LogFormatterPluginManager|null
     */
    protected ?LogFormatterPluginManager $formatterPlugins = null;

    /**
     * Filter chain
     *
     * @var Filter\FilterInterface[]
     */
    protected array $filters = [];

    /**
     * Formats the log message before writing
     *
     * @var Formatter\FormatterInterface|null
     */
    protected ?Formatter\FormatterInterface $formatter;

    /**
     * Use Laminas\Stdlib\ErrorHandler to report errors during calls to write
     *
     * @var bool
     */
    protected bool $convertWriteErrorsToExceptions = true;

    /**
     * Error level passed to Laminas\Stdlib\ErrorHandler::start for errors reported during calls to write
     *
     * @var int
     */
    protected int $errorsToExceptionsConversionLevel = E_WARNING;

    /**
     * Constructor
     *
     * Set options for a writer. Accepted options are:
     * - filters: array of filters to add to this filter
     * - formatter: formatter for this writer
     *
     * @param array|Traversable $options
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = iterator_to_array($options);
        }

        if (is_array($options)) {
            if (isset($options['filter_manager'])) {
                $this->setFilterPluginManager($options['filter_manager']);
            }

            if (isset($options['formatter_manager'])) {
                $this->setFormatterPluginManager($options['formatter_manager']);
            }

            if (isset($options['filters'])) {
                $filters = $options['filters'];
                if (is_int($filters) || is_string($filters) || $filters instanceof Filter\FilterInterface) {
                    $this->addFilter($filters);
                } elseif (is_array($filters)) {
                    foreach ($filters as $filter) {
                        if (is_int($filter) || is_string($filter) || $filter instanceof Filter\FilterInterface) {
                            $this->addFilter($filter);
                        } elseif (is_array($filter)) {
                            if (!isset($filter['name'])) {
                                throw new Exception\InvalidArgumentException(
                                    'Options must contain a name for the filter'
                                );
                            }

                            $filterOptions = $filter['options'] ?? null;
                            $this->addFilter($filter['name'], $filterOptions);
                        }
                    }
                }
            }

            if (isset($options['formatter'])) {
                $formatter = $options['formatter'];
                if (is_string($formatter) || $formatter instanceof Formatter\FormatterInterface) {
                    $this->setFormatter($formatter);
                } elseif (is_array($formatter)) {
                    if (!isset($formatter['name'])) {
                        throw new Exception\InvalidArgumentException('Options must contain a name for the formatter');
                    }

                    $formatterOptions = $formatter['options'] ?? null;
                    $this->setFormatter($formatter['name'], $formatterOptions);
                }
            }
        }
    }

    /**
     * Set filter plugin manager
     *
     * @param string|LogFilterPluginManager $plugins
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setFilterPluginManager($plugins): static
    {
        if (is_string($plugins)) {
            $plugins = new $plugins();
        }

        if (!$plugins instanceof LogFilterPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Writer plugin manager must extend %s; received %s',
                LogFilterPluginManager::class,
                get_debug_type($plugins)
            ));
        }

        $this->filterPlugins = $plugins;
        return $this;
    }

    /**
     * Set formatter plugin manager
     *
     * @param string|LogFormatterPluginManager $plugins
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setFormatterPluginManager($plugins): static
    {
        if (is_string($plugins)) {
            $plugins = new $plugins();
        }

        if (!$plugins instanceof LogFormatterPluginManager) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Writer plugin manager must extend %s; received %s',
                    LogFormatterPluginManager::class,
                    get_debug_type($plugins)
                )
            );
        }

        $this->formatterPlugins = $plugins;
        return $this;
    }

    /**
     * Add a filter specific to this writer.
     *
     * @param int|string|Filter\FilterInterface $filter
     * @param array|null $options
     * @return AbstractWriter
     * @throws Exception\InvalidArgumentException
     */
    public function addFilter($filter, ?array $options = null): static
    {
        if (is_int($filter)) {
            $filter = new Filter\Priority($filter);
        }

        if (is_string($filter)) {
            $filter = $this->filterPlugin($filter, $options);
        }

        if (!$filter instanceof Filter\FilterInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Filter must implement %s\Filter\FilterInterface; received "%s"',
                __NAMESPACE__,
                get_debug_type($filter)
            ));
        }

        $this->filters[] = $filter;
        return $this;
    }

    /**
     * Get filter instance
     *
     * @param string $name
     * @param array|null $options
     * @return Filter\FilterInterface
     */
    public function filterPlugin($name, ?array $options = null): Filter\FilterInterface
    {
        return $this->getFilterPluginManager()->get($name, $options);
    }

    /**
     * Get filter plugin manager
     *
     * @return LogFilterPluginManager
     */
    public function getFilterPluginManager(): ?LogFilterPluginManager
    {
        if (!$this->filterPlugins instanceof \Laminas\Log\FilterPluginManager) {
            $this->setFilterPluginManager(new LogFilterPluginManager(new ServiceManager()));
        }

        return $this->filterPlugins;
    }

    /**
     * Get formatter instance
     *
     * @param string $name
     * @param array|null $options
     * @return Formatter\FormatterInterface
     */
    public function formatterPlugin($name, ?array $options = null): Formatter\FormatterInterface
    {
        return $this->getFormatterPluginManager()->get($name, $options);
    }

    /**
     * Get formatter plugin manager
     *
     * @return LogFormatterPluginManager
     */
    public function getFormatterPluginManager(): ?LogFormatterPluginManager
    {
        if (!$this->formatterPlugins instanceof \Laminas\Log\FormatterPluginManager) {
            $this->setFormatterPluginManager(new LogFormatterPluginManager(new ServiceManager()));
        }

        return $this->formatterPlugins;
    }

    /**
     * Log a message to this writer.
     *
     * @param array $event log data event
     * @return WriterInterface
     */
    public function write(array $event): WriterInterface
    {
        foreach ($this->filters as $filter) {
            if (!$filter->filter($event)) {
                return $this;
            }
        }

        $errorHandlerStarted = false;

        if ($this->convertWriteErrorsToExceptions && !ErrorHandler::started()) {
            ErrorHandler::start($this->errorsToExceptionsConversionLevel);
            $errorHandlerStarted = true;
        }

        try {
            $this->doWrite($event);
        } catch (\Exception $exception) {
            if ($errorHandlerStarted) {
                ErrorHandler::stop();
            }

            throw $exception;
        }

        if ($errorHandlerStarted) {
            $error = ErrorHandler::stop();
            if ($error) {
                throw new Exception\RuntimeException("Unable to write", 0, $error);
            }
        }

        return $this;
    }

    /**
     * Write a message to the log
     *
     * @param array $event log data event
     * @return void
     */
    abstract protected function doWrite(array $event): void;

    /**
     * Set convert write errors to exception flag
     *
     * @param bool $convertErrors
     */
    public function setConvertWriteErrorsToExceptions($convertErrors): void
    {
        $this->convertWriteErrorsToExceptions = $convertErrors;
    }

    /**
     * Perform shutdown activities such as closing open resources
     *
     * @return void
     */
    public function shutdown()
    {
    }

    /**
     * Get formatter
     *
     * @return Formatter\FormatterInterface
     */
    protected function getFormatter(): ?Formatter\FormatterInterface
    {
        return $this->formatter;
    }

    /**
     * Set a new formatter for this writer
     *
     * @param string|Formatter\FormatterInterface $formatter
     * @param array|null $options
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setFormatter($formatter, ?array $options = null): static
    {
        if (is_string($formatter)) {
            $formatter = $this->formatterPlugin($formatter, $options);
        }

        if (!$formatter instanceof Formatter\FormatterInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Formatter must implement %s\Formatter\FormatterInterface; received "%s"',
                __NAMESPACE__,
                get_debug_type($formatter)
            ));
        }

        $this->formatter = $formatter;
        return $this;
    }

    /**
     * Check if the writer has a formatter
     *
     * @return bool
     */
    protected function hasFormatter(): bool
    {
        return $this->formatter instanceof Formatter\FormatterInterface;
    }
}
