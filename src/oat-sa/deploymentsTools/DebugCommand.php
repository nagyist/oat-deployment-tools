<?php 

namespace oat\deploymentsTools;

use ConsoleKit\Command;
use FileSystemCache;
/**
 */
class DebugCommand extends Command {
    public function execute(array $args, array $opts) {
        $key = FileSystemCache::generateCacheKey('script_to_run');
	    $previous = FileSystemCache::retrieve($key);
        $this->writeln('Command to run : ');
        foreach ($previous as $cmd) {
           $this->writeln('$ '  . $cmd);
        }
	}

}