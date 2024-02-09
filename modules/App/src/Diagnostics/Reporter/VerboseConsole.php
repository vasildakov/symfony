<?php

namespace App\Diagnostics\Reporter;

use Laminas\Diagnostics\Runner\Reporter\ReporterInterface;
use ArrayObject;
use Laminas\Console\Adapter\AdapterInterface as Console;
use Laminas\Console\ColorInterface as Color;
use Laminas\Stdlib\StringUtils;
use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\Collection as ResultsCollection;
use Laminas\Diagnostics\Result\FailureInterface as Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\SkipInterface as Skip;
use Laminas\Diagnostics\Result\SuccessInterface as Success;
use Laminas\Diagnostics\Result\WarningInterface as Warning;

class VerboseConsole implements ReporterInterface
{
    /**
     * Console adapter used for displaying output to the screen
     *
     * @var Console
     */
    protected Console $console;

    /**
     * Width of console screen
     *
     * @var int
     */
    protected int $width = 80;

    /**
     * Total number of checks
     *
     * @var int
     */
    protected int $total = 0;

    /**
     * Current iteration in Runner loop.
     *
     * @var int
     */
    protected int $iter = 1;

    /**
     * Should result data be displayed (verbose mode)
     *
     * @var
     */
    protected bool $displayData = false;

    /**
     * Has the checking been stopped before finishing ?
     *
     * @var bool
     */
    protected bool $stopped = false;

    /**
     * Create new instance of reporter
     *
     * @param Console $console     Console adapter to use
     * @param bool    $displayData Should result data be displayed on the screen
     */
    public function __construct(Console $console, $displayData = false)
    {
        $this->console = $console;
        $this->stringUtils = StringUtils::getWrapper();
        $this->displayData = $displayData;
    }

    /**
     * This method is called right after Reporter starts running, via Runner::run()
     *
     * @param  ArrayObject $checks       A collection of Checks that will be performed
     * @param  array       $runnerConfig Complete Runner configuration, obtained via Runner::getConfig()
     * @return void
     */
    public function onStart(ArrayObject $checks, $runnerConfig): void
    {
        $this->stopped = false;
        $this->width = $this->console->getWidth();
        $this->total = $checks->count();

        $this->console->writeLine('Starting diagnostics:');
    }

    /**
     * This method is called before each individual Check is performed. If this
     * method returns false, the Check will not be performed (will be skipped).
     *
     * @param  CheckInterface $check Check instance that is about to be performed.
     * @param  bool           $alias The alias being targeted by the check
     * @return bool|void      Return false to prevent check from happening
     */
    public function onBeforeRun(CheckInterface $check, $alias = null)
    {
    }

    /**
     * This method is called every time a Check has been performed. If this method
     * returns false, the Runner will not perform any additional checks and stop
     * its run.
     *
     * @param  CheckInterface  $check  A Check instance that has just finished running
     * @param  ResultInterface $result Result for that particular check instance
     * @param  bool            $alias  The alias being targeted by the check
     * @return bool|void       Return false to prevent from running additional Checks
     */
    public function onAfterRun(CheckInterface $check, ResultInterface $result, $alias = null)
    {
        $descr = ' ' . $check->getLabel();
        if ($message = $result->getMessage()) {
            $descr .= ': ' . $result->getMessage();
        }

        if ($this->displayData && ($data = $result->getData())) {
            $descr .= PHP_EOL . str_repeat('-', $this->width - 7);
            $data = $result->getData();
            if (is_object($data) && $data instanceof \Exception) {
                $descr .= PHP_EOL . get_class($data) . PHP_EOL . $data->getMessage() . $data->getTraceAsString();
            } else {
                $descr .= PHP_EOL . @var_export($result->getData(), true);
            }

            $descr .= PHP_EOL . str_repeat('-', $this->width - 7);
        }

        // Draw status line
        if ($result instanceof Success) {
            $this->console->write('  OK  ', Color::WHITE, Color::GREEN);
            $this->console->writeLine(
                $this->strColPad(
                    $descr,
                    $this->width - 7,
                    '       '
                ),
                Color::GREEN
            );
        } elseif ($result instanceof Failure) {
            $this->console->write(' FAIL ', Color::WHITE, Color::RED);
            $this->console->writeLine(
                $this->strColPad(
                    $descr,
                    $this->width - 7,
                    '       '
                ),
                Color::RED
            );
        } elseif ($result instanceof Warning) {
            $this->console->write(' WARN ', Color::NORMAL, Color::YELLOW);
            $this->console->writeLine(
                $this->strColPad(
                    $descr,
                    $this->width - 7,
                    '       '
                ),
                Color::YELLOW
            );
        } elseif ($result instanceof Skip) {
            $this->console->write(' SKIP ', Color::NORMAL, Color::YELLOW);
            $this->console->writeLine(
                $this->strColPad(
                    $descr,
                    $this->width - 7,
                    '       '
                ),
                Color::YELLOW
            );
        } else {
            $this->console->write(' ???? ', Color::NORMAL, Color::YELLOW);
            $this->console->writeLine(
                $this->strColPad(
                    $descr,
                    $this->width - 7,
                    '       '
                ),
                Color::YELLOW
            );
        }
    }

    /**
     * This method is called when Runner has been aborted and could not finish the
     * whole run().
     *
     * @param  ResultsCollection $results Collection of Results for performed Checks.
     * @return void
     */
    public function onStop(ResultsCollection $results): void
    {
        $this->stopped = true;
    }

    /**
     * This method is called when Runner has finished its run.
     *
     * @param  ResultsCollection $results Collection of Results for performed Checks.
     * @return void
     */
    public function onFinish(ResultsCollection $results): void
    {
        $this->console->writeLine();

        // Display information that the check has been aborted.
        if ($this->stopped) {
            $this->console->writeLine('Diagnostics aborted because of a failure.', Color::RED);
        }

        // Display a summary line
        if ($results->getFailureCount() == 0 && $results->getWarningCount() == 0 && $results->getUnknownCount() == 0) {
            $line = 'OK (' . $this->total . ' diagnostic checks)';
            $this->console->writeLine(
                str_pad($line, $this->width - 1, ' ', STR_PAD_RIGHT),
                Color::NORMAL,
                Color::GREEN
            );
        } elseif ($results->getFailureCount() == 0) {
            $line = $results->getWarningCount() . ' warnings, ';
            $line .= $results->getSuccessCount() . ' successful checks';

            if ($results->getSkipCount() > 0) {
                $line .= ', ' . $results->getSkipCount() . ' skipped checks';
            }

            if ($results->getUnknownCount() > 0) {
                $line .= ', ' . $results->getUnknownCount() . ' unknown check results';
            }

            $line .= '.';

            $this->console->writeLine(
                str_pad($line, $this->width - 1, ' ', STR_PAD_RIGHT),
                Color::NORMAL,
                Color::YELLOW
            );
        } else {
            $line = $results->getFailureCount() . ' failures, ';
            $line .= $results->getWarningCount() . ' warnings, ';
            $line .= $results->getSuccessCount() . ' successful checks';

            if ($results->getSkipCount() > 0) {
                $line .= ', ' . $results->getSkipCount() . ' skipped checks';
            }

            if ($results->getUnknownCount() > 0) {
                $line .= ', ' . $results->getUnknownCount() . ' unknown check results';
            }

            $line .= '.';

            $this->console->writeLine(
                str_pad($line, $this->width, ' ', STR_PAD_RIGHT),
                Color::NORMAL,
                Color::RED
            );
        }

        $this->console->writeLine();
    }

    /**
     * Set Console adapter to use
     *
     * @param Console $console
     */
    public function setConsole(Console $console): void
    {
        $this->console = $console;

        // Update width
        $this->width = $console->getWidth();
    }

    /**
     * Get currently used Console adapter
     *
     * @return Console
     */
    public function getConsole(): Console
    {
        return $this->console;
    }

    /**
     * Set if result data should be displayed on the screen
     *
     * @param bool $displayData
     */
    public function setDisplayData($displayData): void
    {
        $this->displayData = (bool) $displayData;
    }

    /**
     * Get the flag value, if result data should be displayed on the screen
     * @return bool
     */
    public function getDisplayData(): bool
    {
        return $this->displayData;
    }

    /**
     * Apply padding and word-wrapping for a string.
     *
     * @param  string $string  The string to transform
     * @param  int    $width   Maximum width at which the string should be wrapped to the next line
     * @param  int    $padding The left-side padding to apply
     * @return string The padded and wrapped string
     */
    protected function strColPad($string, $width, $padding): string
    {
        $string = $this->stringUtils->wordWrap($string, $width, PHP_EOL, true);
        $lines = explode(PHP_EOL, $string);
        for ($x = 1; $x < count($lines); $x++) {
            $lines[$x] = $padding . $lines[$x];
        }

        return implode(PHP_EOL, $lines);
    }
}