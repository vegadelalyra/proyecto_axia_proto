<?php
/**
 * APC queue
 * @package apc_queue
 * @author Julius Beckmann 
 * @link http://juliusbeckmann.de/classes/apc_queue/
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @filesource
 */

/**
 * Simple queue interface
 * @package apc_queue
 */
interface queue_interface{
    public function add($value);
    public function get();
    public function length();
}

/**
 * Simple class for fifo queues saved in APC cache
 * @name APC queue
 * @version v0.1_2010.01.29
 * @access public
 * @package apc_queue
 *      
 *      Copyright 2009 Julius Beckmann
 *      
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */
class apc_queue implements queue_interface {
    
    /**
     * Overall prefix for queue keys
     */
    private $prefix = 'APC_QUEUE_';
    /**
     * Name of this queue
     */
    private $name = '';
    
    /**
     * Key prefix
     */
    private $key = NULL;
    
    /**
     * Key of head counter
     */
    private $head = NULL;

    /**
     * Key of tail counter
     */
    private $tail = NULL;
    
    /**
     * Creates queue object. Every queue needs a name.
     * @param string $queue_name
     * @param boolean $force_new 
     */
    public function __construct($queue_name, $force_new=FALSE) {
        $this->name = (string)$queue_name;
        // Defined counter keys
        $this->head = $this->prefix.$this->name.'_head';
        $this->tail = $this->prefix.$this->name.'_tail';
        $this->key  = $this->prefix.$this->name.'_';
        $this->init($force_new);
    }
    
    /**
     * Initializes the queue and checks for counter integrity
     * @access private
     * @param boolean $force_new
     * @return boolean
     */
    private function init($force_new=FALSE) {
        if($force_new) {
            // Force new queue by resettings counters
            apcu_store($this->head, 0);
            apcu_store($this->tail, 0);
        }else{
            // Check head counter
            $success = FALSE;
            $val_head = apcu_fetch($this->head, $success); 
            if(!$success) {
                apcu_store($this->head, 0);
            }
            // Check tail counter
            $success = FALSE;
            $val_tail = apcu_fetch($this->tail, $success); 
            if(!$success) {
                apcu_store($this->tail, 0);
            }   
            // Check counter integrity
            if($val_head < $val_tail) {
                // Force new queue
                $this->init(TRUE);
            }
        }
        return TRUE;
    }
    
    /**
     * Adds a value to queue
     * @param mixed $value
     */
    public function add($value) {
        $id = apcu_fetch($this->head)+1;
        apcu_store($this->head, $id);
        apcu_store($this->key.$id, $value);
        return TRUE;
    }
    
    /**
     * Fetches next value from queue. FALSE if empty.
     * @return mixed
     */
    public function get() {
        // Check length
        if($this->length() < 1) {
            // Reset counters if queue is empty
            $this->init(TRUE);
            return FALSE;
        }
        $id = apcu_fetch($this->tail)+1;
        apcu_store($this->tail, $id);
        return apcu_fetch($this->key.$id);
    }
    
    /**
     * Returns queue length
     * @return integer
     */
    public function length() {
        $head = apcu_fetch($this->head);
        $tail = apcu_fetch($this->tail);
        $length = $head - $tail;
        return ($length >= 0) ? $length : 0 ;
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
