<?php
namespace FMUP\Import;

use FMUP\Import\Iterator\DuplicateIterator;
use FMUP\Import\Iterator\LineToConfigIterator;
use FMUP\Import\Iterator\ValidatorIterator;

/**
 *
 * @author csanz
 *
 */
abstract class Display extends \FMUP\Import
{
    private $totalInsert;
    private $totalUpdate;
    private $totalErrors;

    /**
     *
     * @return int
     */
    public function getTotalUpdate()
    {
        return (int)$this->totalUpdate;
    }

    /**
     *
     * @return int
     */
    public function getTotalInsert()
    {
        return (int)$this->totalInsert;
    }

    /**
     *
     * @return int
     */
    public function getTotalErrors()
    {
        return (int)$this->totalErrors;
    }

    public function parse()
    {
        try {
            $lci = $this->getLineToConfigIterator($this->fileIterator, $this->config);
            $di = $this->getDoublonIterator($lci);
            $vi = $this->getValidatorIterator($di);
            foreach ($vi as $key => $value) {
                if ($value) {
                    $this->displayImport($value, $vi, $di, $lci, $key);
                }
            }
            $this->totalErrors = $vi->getTotalErrors();
            $this->totalInsert = $vi->getTotalInsert();
            $this->totalUpdate = $vi->getTotalUpdate();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Affiche l'import
     *
     * @param Config $value
     * @param ValidatorIterator $vi
     * @param DuplicateIterator $di
     * @param LineToConfigIterator $lci
     * @param int $key
     */
    abstract public function displayImport(
        Config $value,
        ValidatorIterator $vi,
        DuplicateIterator $di,
        LineToConfigIterator $lci,
        $key
    );

    /**
     * @param \Iterator $fIterator
     * @param Config $config
     * @return LineToConfigIterator
     * @codeCoverageIgnore
     */
    protected function getLineToConfigIterator(\Iterator $fIterator, \FMUP\Import\Config $config)
    {
        return new LineToConfigIterator($fIterator, $config);
    }

    /**
     * @param \Traversable $iterator
     * @return DuplicateIterator
     * @codeCoverageIgnore
     */
    protected function getDoublonIterator(\Traversable $iterator)
    {
        return new DuplicateIterator($iterator);
    }

    /**
     * @param \Traversable $iterator
     * @return ValidatorIterator
     * @codeCoverageIgnore
     */
    protected function getValidatorIterator(\Traversable $iterator)
    {
        return new ValidatorIterator($iterator);
    }
}
