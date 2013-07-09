<?php
namespace Bacon\Shell;

use Bacon\Shell\Lock\Exception;

class Lock {
	
	protected $lockfile;
	protected $pid;
	protected $handle;
	
	/**
	 * 
	 * @param string $lockfile
	 * @param int $pid
	 */
	public function __construct($lockfile, $pid) {
		$this->lockfile = $lockfile;
		$this->pid = $pid;
	}

	/**
	 * 
	 * @param int $pid
	 * @return boolean
	 */
	protected function isRunning($pid) {
		$procs = explode(PHP_EOL, `ps -e | awk '{print $1}'`);	
        return in_array($pid, $procs);
    }
		
    /**
     * Aquires lock
     * 
     * @throws Exception
     */
	public function aquire() {
		if(!$this->handle = @fopen($this->lockfile, "x+")) {
			
			if(!$this->handle = fopen($this->lockfile, "r")) {
				throw new Exception('Failed to aquire read lock');
			}			
			$pid = fgets($this->handle);
									
			if($pid > 0 && $this->isRunning($pid)) {
				throw new Exception('File locked by process id:'.$pid);
			} else {
				if(!$this->handle = fopen($this->lockfile, "w")) {
					throw new Exception('Failed to aquire writelock');
				}
			}
		}
 		fwrite($this->handle, $this->pid);
 		fclose($this->handle);
	}
	
	/**
	 * Releases lock
	 */
	public function release() {
		unlink($this->lockfile);
	}
	
	
	public function __destruct() {
		@fclose($this->handle);
	}
}