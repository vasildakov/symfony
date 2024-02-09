<?php

namespace App\Diagnostics\Reporter;

use ArrayObject;
use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\Collection as ResultsCollection;
use Laminas\Diagnostics\Result\FailureInterface;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\SuccessInterface;
use Laminas\Diagnostics\Result\WarningInterface;
use Laminas\Diagnostics\Runner\Reporter\ReporterInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SymfonyConsole implements ReporterInterface
{
    /**
     * Total number of Checks
     *
     * @var int
     */
    protected int $total = 0;

    /**
     * Current Runner iteration
     *
     * @var int
     */
    protected int $iteration = 1;

    /**
     * Current vertical screen position
     *
     * @var int
     */
    protected int $pos = 1;

    protected $progressBar;


    protected bool $stopped = false;

    public function __construct(
        private InputInterface $input,
        private OutputInterface $output
    ) {

    }

    public function onStart(ArrayObject $checks, $runnerConfig): void
    {
        $this->output->writeln('');
        $this->output->writeln('<fg=green>Starting diagnostics:</>');
        //$this->output->writeln('');

        $this->stopped   = false;
        $this->iteration = 1;
        $this->total     = count($checks);
    }

    public function onBeforeRun(CheckInterface $check, $checkAlias = null)
    {
    }

    public function onAfterRun(CheckInterface $check, ResultInterface $result, $checkAlias = null): void
    {
        $this->iteration++;
    }

    public function onStop(ResultsCollection $results): void
    {
        $this->stopped = true;
    }

    public function onFinish(ResultsCollection $results): void
    {
        $this->output->writeln('');
        // Display a summary line
        if (
            $results->getFailureCount() === 0
            && $results->getWarningCount() === 0
            && $results->getUnknownCount() === 0
            && $results->getSkipCount() === 0
        ) {
            $line = 'OK (' . $this->total . ' diagnostic tests)';
            $this->output->writeln(sprintf('<fg=black;bg=green> %s </>', $line));
            $this->output->writeln('');

        } elseif ($results->getFailureCount() === 0) {
            $line  = $results->getWarningCount() . ' warnings, ';
            $line .= $results->getSuccessCount() . ' successful tests';

            if ($results->getSkipCount() > 0) {
                $line .= ', ' . $results->getSkipCount() . ' skipped tests';
            }

            if ($results->getUnknownCount() > 0) {
                $line .= ', ' . $results->getUnknownCount() . ' unknown test results';
            }

            $line .= '.';

            $this->output->writeln(sprintf('<fg=white;bg=orange> %s </>', $line));
        } else {
            $line  = $results->getFailureCount() . ' failures, ';
            $line .= $results->getWarningCount() . ' warnings, ';
            $line .= $results->getSuccessCount() . ' successful tests';

            if ($results->getSkipCount() > 0) {
                $line .= ', ' . $results->getSkipCount() . ' skipped tests';
            }

            if ($results->getUnknownCount() > 0) {
                $line .= ', ' . $results->getUnknownCount() . ' unknown test results';
            }

            $line .= '.';

            $this->output->writeln(sprintf('<fg=white;bg=red> %s </>', $line));
        }

        foreach ($results as $check) {
            $result = $results[$check];
            if ($result instanceof SuccessInterface) {
                $this->output->writeln(sprintf(
                    '<fg=black;bg=green> OK </> <fg=green> %s: %s </>',
                    $check->getLabel(),
                    $result->getMessage())
                );

            } elseif ($result instanceof FailureInterface) {
                $this->output->writeln(sprintf(
                        '<fg=white;bg=red> Failure </> <fg=red> %s: %s </>',
                        $check->getLabel(),
                        $result->getMessage())
                );

            } elseif ($result instanceof WarningInterface) {
                $this->output->writeln('Warning: ' . $check->getLabel());
                $message = $result->getMessage();
                if ($message) {
                    $this->output->writeln($message);
                }
            } elseif (! $result instanceof SuccessInterface) {
                $this->output->writeln('Unknown result ' . $result::class . ': ' . $check->getLabel());
                $message = $result->getMessage();
                if ($message) {
                    $this->output->writeln($message);
                }
            }
        }
        $this->output->writeln('');
    }
}