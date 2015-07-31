<?php

namespace oat\deploymentsTools;

use ConsoleKit\Command;
use ConsoleKit\Widgets\Dialog;
use FileSystemCache;

class CleanStackCommand extends Command {
    public function execute(array $args, array $opts) {
        $dialog = new Dialog($this->console);
        if ($dialog->confirm('This will wipe all your cache, are you sure?')) {
            $key = FileSystemCache::generateCacheKey('script_to_run');

            FileSystemCache::invalidate($key);
            $this->console->writeln("all task have been wiped");
        }    
    }
}
