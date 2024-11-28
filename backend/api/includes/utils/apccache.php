<?php
/**
 * APC cache
 * @package apc_cache
 * @author David Fernandez
 * @filesource
 */

/**
 * Simple queue interface
 * @package apc_queue
 */
interface cache_interface{
    public function set($key, $value);
    public function get($key);
    public function exists($key);
    public function delete($key);
}


class apc_cache implements cache_interface {
    
    /**
     * Overall prefix for queue keys
     */
    private $prefix = 'APC_CACHE_';

    
    /**
     * Creates queue object. Every queue needs a name.
     * @param string $queue_name
     * @param boolean $force_new 
     */
    public function __construct() {
    }
    
    /**
     * Adds a value to queue
     * @param mixed $value
     */
    public function set($key, $value) {
        
        return apcu_store($this->prefix.$key, $value);
    }
    
    /**
     * Fetches next value from queue. FALSE if empty.
     * @return mixed
     */
    public function get($key) {
        return apcu_fetch($this->prefix.$key);
    }
    
    public function exists($key) {
        return apcu_exists($this->prefix.$key);
    }

    public function delete($key) {
        return apcu_delete($this->prefix.$key);
    }
    
}

// Check if APC has some memory left
// var_dump(apc_sma_info());

// Example:

/*
$q = new apc_queue('test', isset($_GET['force']));

echo "LENGTH: ", $q->length(), "\nSTORE: ";
for($i=0; $i<10; ++$i) {
    $rand = rand(0,9);
    echo $rand,',';
    $q->add($rand);
}
echo "\nLENGTH: ", $q->length(), "\nFETCH: ";
while(($g = $q->get()) !== FALSE) {
    echo $g,',';
}
echo "\nLENGTH: ", $q->length();

*/
