<?php 

namespace oat\deploymentsTools;

use ConsoleKit\Command;
use FileSystemCache;
/**
 */
class ExecCommand extends Command {
    public function execute(array $args, array $opts) {
        $key = FileSystemCache::generateCacheKey('script_to_run');
	    $previous = FileSystemCache::retrieve($key);
	    if(!empty($previous)) {
	    	$toRunAgain = array();
	        $this->writeln('Command to run : ');
	        foreach ($previous as $path) {
	        	if(is_dir($path)) {
		        	$cmd = 'cd ' . $path . ' && vendor/bin/taoTools.sh ' . $path . ' platform_update'; 
		            $this->writeln('$ '  . $cmd);
		            exec($cmd,$output,$return);

		            if($return !== 0 ) {
		            	$toRunAgain[] = $path;
		            }
		        }
		        else {
		        	$this->writeerr('path ' . $path . ' is not a valid dir skipped' . PHP_EOL);
		        }
                   
	        }
	        FileSystemCache::store($key, $toRunAgain);
		        
    	}
    	else {
    		$this->writeln('Nothing to run, have a nice day :)');
    	}
	}

}