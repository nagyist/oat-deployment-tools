<?php 

namespace oat\deploymentsTools;

use ConsoleKit\Command;
use FileSystemCache;
/**
 */
class AddCommand extends Command {
    public function execute(array $args, array $opts) {

    	if(is_array($args) && !empty($args)) {
	    	$key = FileSystemCache::generateCacheKey('script_to_run');
	    	$previous = FileSystemCache::retrieve($key);

	    	$store = is_array($previous) ? array_merge($previous,$args)  : $args;
	    	FileSystemCache::store($key, $store);

	    	$this->writeln('add command to run '  . implode($args, PHP_EOL) );
    	}
    	else {
    		$this->writeerr('command parameter is missing' . PHP_EOL );
    	}
    }
}