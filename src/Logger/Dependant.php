<?php
/**
 * This trait provides functionality common to all classes dependant on the \Maleficarum\Api\Logger namespace
 */
declare (strict_types=1);

namespace Maleficarum\Logger;

trait Dependant {
    
    /* ------------------------------------ Class Property START --------------------------------------- */
    
    /**
     * Internal storage for the logger object.
     *
     * @var \Psr\Log\LoggerInterface|null
     */
    protected $logger = null;

    /* ------------------------------------ Class Property END ----------------------------------------- */
    
    /* ------------------------------------ Class Methods START ---------------------------------------- */
    
    /**
     * Inject a new logger object.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @return \Maleficarum\Logger\Dependant
     */
    public function setLogger(\Psr\Log\LoggerInterface $logger) {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Fetch the currently assigned logger object.
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger() {
        return $this->logger;
    }

    /**
     * Detach the currently assigned logger object.
     *
     * @return \Maleficarum\Logger\Dependant
     */
    public function detachLogger() {
        $this->logger = null;

        return $this;
    }
    
    /* ------------------------------------ Class Methods END ------------------------------------------ */
    
}
