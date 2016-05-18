<?php
/**
 * Magento Simple Asyncronous Queuing Module
 *
 * @category    Lilmuckers
 * @package     Lilmuckers_Queue
 * @copyright   Copyright (c) 2013 Patrick McKinley (http://www.patrick-mckinley.com)
 * @license     http://choosealicense.com/licenses/mit/
 */

/**
 * The queue adaptor abstract
 *
 * @category Lilmuckers
 * @package  Lilmuckers_Queue
 * @author   Patrick McKinley <contact@patrick-mckinley.com>
 * @license  MIT http://choosealicense.com/licenses/mit/
 * @link     https://github.com/lilmuckers/magento-lilmuckers_queue
 */
abstract class Lilmuckers_Queue_Model_Adapter_Abstract extends Varien_Object
{
    /**
     * Run Inline Flag
     * 
     * This is used to flag if the adapter will run the function inline itself.
     * This is mostly for systems such as Gearman that work off direct callbacks
     * rather than purely messaging back and forth.
     * 
     * @var bool
     */
    protected $_runInline = false;
    
    /**
     * Return the runInline flag
     * 
     * @return bool
     */
    public function getRunInline()
    {
        return $this->_runInline;
    }
    
    /**
     * If the adapter is to be be run inline, this is the method to call
     * 
     * @param array $queues The queues to attach to
     * 
     * @return Lilmuckers_Queue_Model_Adapter_Abstract
     */
    public function run($queues)
    {
        return $this;
    }
    
    /**
     * Add a task to the queue
     * 
     * @param Lilmuckers_Queue_Model_Queue      $queue The queue identifier
     * @param Lilmuckers_Queue_Model_Queue_Task $task  The task to queue
     * 
     * @return Lilmuckers_Queue_Model_Queue_Abstract
     */
    public function addTask(
        Lilmuckers_Queue_Model_Queue_Abstract $queue, 
        Lilmuckers_Queue_Model_Queue_Task $task
    )
    {
        //ensure the queue connection is loaded
        $this->_loadConnection();
        
        //queue this stuff up
        $this->_addToQueue($queue->getName(), $task);
        
        return $this;
    }
    
    /**
     * Load the connection handler
     * 
     * @return Lilmuckers_Queue_Model_Adapter_Abstract
     */
    abstract protected function _loadConnection();
    
    /**
     * Add the task to the queue
     * 
     * @param string                            $queue The queue identifier
     * @param Lilmuckers_Queue_Model_Queue_Task $task  The task to queue
     * 
     * @return Lilmuckers_Queue_Model_Adapter_Abstract
     */
    abstract protected function _addToQueue(
        $queue, 
        Lilmuckers_Queue_Model_Queue_Task $task
    );
    
    /**
     * Get the task object for the queue in question
     * 
     * @param string $queue The queue identifier
     * 
     * @return Lilmuckers_Queue_Model_Queue_Task
     */
    abstract protected function _reserveFromQueue($queue);
    
    /**
     * Get the next task from the provided queue or array of queues
     * 
     * @param mixed $queue The queue identifier
     * 
     * @return Lilmuckers_Queue_Model_Queue_Task
     */
    public function getTask($queue)
    {
        if (is_array($queue)) {
            //retrieve and reserve the next job from a list of queues
            $_task = $this->_reserveFromQueues($queue);
        } else {
            $_task = $this->_reserveFromQueue($queue);
        }
        return $_task;
    }
    
    /**
     * Get the next task object for the queues in question
     * 
     * @param array $queues The queue identifiers
     * 
     * @return Lilmuckers_Queue_Model_Queue_Task
     */
    abstract protected function _reserveFromQueues($queues);
    
    /**
     * Touch the task to keep it reserved
     * 
     * @param Lilmuckers_Queue_Model_Queue_Task $task The task to renew
     * 
     * @return Lilmuckers_Queue_Model_Adapter_Abstract
     */
    public function touch(Lilmuckers_Queue_Model_Queue_Task $task)
    {
        $this->_touch($task);
        return $this;
    }
    
    /**
     * Touch the task to keep it reserved
     * 
     * @param Lilmuckers_Queue_Model_Queue_Task $task The task to renew
     * 
     * @return Lilmuckers_Queue_Model_Adapter_Abstract
     */
    abstract protected function _touch(Lilmuckers_Queue_Model_Queue_Task $task);
    
    /**
     * Remove a task from the queue
     * 
     * @param Lilmuckers_Queue_Model_Queue_Abstract $queue The queue handler for the queue
     * @param Lilmuckers_Queue_Model_Queue_Task     $task  The task handler to remove
     * 
     * @return Lilmuckers_Queue_Model_Adapter_Abstract
     */
    public function remove(
        Lilmuckers_Queue_Model_Queue_Abstract $queue, 
        Lilmuckers_Queue_Model_Queue_Task $task
    )
    {
        $this->_remove($queue, $task);
        return $this;
    }
    
    /**
     * Remove a task from the queue abstract method
     * 
     * @param Lilmuckers_Queue_Model_Queue_Abstract $queue The queue handler to use
     * @param Lilmuckers_Queue_Model_Queue_Task     $task  The task handler to remove
     * 
     * @return Lilmuckers_Queue_Model_Adapter_Abstract
     */
    abstract protected function _remove(
        Lilmuckers_Queue_Model_Queue_Abstract $queue, 
        Lilmuckers_Queue_Model_Queue_Task $task
    );
    
    /**
     * Hold a task in the queue
     * 
     * @param Lilmuckers_Queue_Model_Queue_Abstract $queue The queue handler to use
     * @param Lilmuckers_Queue_Model_Queue_Task     $task  The task to hold
     * 
     * @return Lilmuckers_Queue_Model_Adapter_Abstract
     */
    public function hold(
        Lilmuckers_Queue_Model_Queue_Abstract $queue, 
        Lilmuckers_Queue_Model_Queue_Task $task
    )
    {
        $this->_hold($queue, $task);
        return $this;
    }
    
    /**
     * Hold a task in the queue abstract method
     * 
     * @param Lilmuckers_Queue_Model_Queue_Abstract $queue The queue handler to use
     * @param Lilmuckers_Queue_Model_Queue_Task     $task  The task to hold
     * 
     * @return Lilmuckers_Queue_Model_Adapter_Abstract
     */
    abstract protected function _hold(
        Lilmuckers_Queue_Model_Queue_Abstract $queue, 
        Lilmuckers_Queue_Model_Queue_Task $task
    );
    
    /**
     * Unhold a number of jobs
     * 
     * @param int                                   $number The number of held tasks to kick
     * @param Lilmuckers_Queue_Model_Queue_Abstract $queue  The queue handler to use
     * 
     * @return Lilmuckers_Queue_Model_Adapter_Abstract
     */
    public function unholdMultiple(
        $number, 
        Lilmuckers_Queue_Model_Queue_Abstract $queue = null
    )
    {
        return $this->_unholdMultiple($number, $queue);
    }
    
    /**
     * Unhold a number of jobs directly with the backend
     * 
     * @param int                                   $number The number of held tasks to kick
     * @param Lilmuckers_Queue_Model_Queue_Abstract $queue  The queue handler to use
     * 
     * @return Lilmuckers_Queue_Model_Adapter_Abstract
     */
    abstract public function _unholdMultiple(
        $number, 
        Lilmuckers_Queue_Model_Queue_Abstract $queue = null
    );
    
    /**
     * Unhold a task in the queue 
     * 
     * @param Lilmuckers_Queue_Model_Queue_Abstract $queue The queue handler to use
     * @param Lilmuckers_Queue_Model_Queue_Task     $task  The task to unhold
     * 
     * @return Lilmuckers_Queue_Model_Adapter_Abstract
     */
    public function unhold(
        Lilmuckers_Queue_Model_Queue_Abstract $queue, 
        Lilmuckers_Queue_Model_Queue_Task $task
    ) 
    {
        $this->_unhold($queue, $task);
        return $this;
    }
    
    /**
     * Unhold a task in the queue - abstract method
     * 
     * @param Lilmuckers_Queue_Model_Queue_Abstract $queue The queue handler to use
     * @param Lilmuckers_Queue_Model_Queue_Task     $task  The task to unhold
     * 
     * @return Lilmuckers_Queue_Model_Adapter_Abstract
     */
    abstract protected function _unhold(
        Lilmuckers_Queue_Model_Queue_Abstract $queue, 
        Lilmuckers_Queue_Model_Queue_Task $task
    );
    
    /**
     * Requeue a task 
     * 
     * @param Lilmuckers_Queue_Model_Queue_Abstract $queue The queue handler to use
     * @param Lilmuckers_Queue_Model_Queue_Task     $task  The task to retry
     * 
     * @return Lilmuckers_Queue_Model_Adapter_Abstract
     */
    public function retry(
        Lilmuckers_Queue_Model_Queue_Abstract $queue, 
        Lilmuckers_Queue_Model_Queue_Task $task
    ) 
    {
        $this->_retry($queue, $task);
        return $this;
    }
    
    /**
     * Requeue a task - abstract method
     * 
     * @param Lilmuckers_Queue_Model_Queue_Abstract $queue The queue handler to work within
     * @param Lilmuckers_Queue_Model_Queue_Task     $task  The task to retry
     * 
     * @return Lilmuckers_Queue_Model_Adapter_Abstract
     */
    abstract protected function _retry(
        Lilmuckers_Queue_Model_Queue_Abstract $queue, 
        Lilmuckers_Queue_Model_Queue_Task $task
    );
    
    /**
     * Get the meta information for a given task
     * 
     * @param Lilmuckers_Queue_Model_Queue_Task $task The task to get information about
     * 
     * @return Varien_Object
     */
    public function getInformation(Lilmuckers_Queue_Model_Queue_Task $task)
    {
        //load an array of the data, pre-mapped
        $_data = $this->_getMappedTaskData($task);
        
        //import it into a Varien_Object and return it
        $_taskData = new Varien_Object($_data);
        return $_taskData;
    }
    
    /**
     * Get the job meta information, mapped to the standard fields of:
     * 
     * queue => The queue code
     * state => The current task state
     * priority => The current priority
     * age => How long it's been in the system (seconds)
     * delay => How long it's execution offset is (seconds)
     * ttr => The TTR for the task
     * expiration => If the job is reserved - how long before it's returned to the queue
     * reserves => The number of times this has been reserved
     * timeouts => The number of times the task has timed out and been returned ot the queue
     * releases => The number of times the task has been manually returned to the queue
     * holds => The number of times the task has been held
     * unholds => The number of times the task has been unheld
     * 
     * @param Lilmuckers_Queue_Model_Queue_Task $task The task to map the data from
     * 
     * @return array
     */
    abstract protected function _getMappedTaskData(Lilmuckers_Queue_Model_Queue_Task $task);
    
    /**
     * Get the next job in the queue without reserving it
     * 
     * @param Lilmuckers_Queue_Model_Queue_Abstract $queue Queue to peek at
     * 
     * @return Lilmuckers_Queue_Model_Queue_Task
     */
    public function getUnreservedTask(Lilmuckers_Queue_Model_Queue_Abstract $queue)
    {
        return $this->_getUnreservedTask($queue);
    }
    
    /**
     * Get the next job in teh queue without reserving it
     * 
     * @param Lilmuckers_Queue_Model_Queue_Abstract $queue Queue to peek at
     * 
     * @return Lilmuckers_Queue_Model_Queue_Task
     */
    abstract protected function _getUnreservedTask(
        Lilmuckers_Queue_Model_Queue_Abstract $queue
    );
    
    /**
     * Get the next delayed job in the queue without reserving it
     * 
     * @param Lilmuckers_Queue_Model_Queue_Abstract $queue Queue to peek at
     * 
     * @return Lilmuckers_Queue_Model_Queue_Task
     */
    public function getUnreservedDelayedTask(Lilmuckers_Queue_Model_Queue_Abstract $queue)
    {
        return $this->_getUnreservedDelayedTask($queue);
    }
    
    /**
     * Get the next delayed job in the queue without reserving it
     * 
     * @param Lilmuckers_Queue_Model_Queue_Abstract $queue Queue to peek at
     * 
     * @return Lilmuckers_Queue_Model_Queue_Task
     */
    abstract protected function _getUnreservedDelayedTask(
        Lilmuckers_Queue_Model_Queue_Abstract $queue
    );
    
    /**
     * Get the next held job in the queue without reserving it
     * 
     * @param Lilmuckers_Queue_Model_Queue_Abstract $queue Queue to peek at
     * 
     * @return Lilmuckers_Queue_Model_Queue_Task
     */
    public function getUnreservedHeldTask(Lilmuckers_Queue_Model_Queue_Abstract $queue)
    {
        return $this->_getUnreservedDelayedTask($queue);
    }
    
    /**
     * Get the next held job in the queue without reserving it
     * 
     * @param Lilmuckers_Queue_Model_Queue_Abstract $queue Queue to peek at
     * 
     * @return Lilmuckers_Queue_Model_Queue_Task
     */
    abstract protected function _getUnreservedHeldTask(
        Lilmuckers_Queue_Model_Queue_Abstract $queue
    );
    
    /**
     * Close the database connection
     * 
     * @return Lilmuckers_Queue_Model_Adapter_Abstract
     */
    public function closeDbConnection()
    {
        Mage::dispatchEvent('lilmuckers_queue_close_db_connection_before');
        $_db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $_db->closeConnection();
        Mage::dispatchEvent('lilmuckers_queue_close_db_connection_after', array('connection' => $_db));
        return $this;
    }
    
    /**
     * Reopen the database connection
     * 
     * @return Lilmuckers_Queue_Model_Adapter_Abstract
     */
    public function reopenDbConnection()
    {
        Mage::dispatchEvent('lilmuckers_queue_reopen_db_connection_before');
        $_db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $_db->getConnection();
        Mage::dispatchEvent('lilmuckers_queue_reopen_db_connection_after', array('connection' => $_db));
        return $this;
    }
}
