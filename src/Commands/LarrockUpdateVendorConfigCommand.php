<?php

namespace Larrock\Core\Commands;

use Illuminate\Console\Command;

class LarrockUpdateVendorConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larrock:updateVendorConfig';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update vendor configs';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('=== Update vendor configs ===');

        $dir = dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'configVendor'. DIRECTORY_SEPARATOR;

        \File::copy($dir . 'auth.php', base_path('/config/auth.php'));
        \File::copy($dir . 'breadcrumbs.php', base_path('/config/breadcrumbs.php'));
        \File::copy($dir . 'filesystems.php', base_path('/config/filesystems.php'));
        \File::copy($dir . 'jsvalidation.php', base_path('/config/jsvalidation.php'));
        \File::copy($dir . 'medialibrary.php', base_path('/config/medialibrary.php'));
        \File::copy($dir . 'cart.php', base_path('/config/cart.php'));
        \File::copy($dir . 'database.php', base_path('/config/database.php'));
        \File::deleteDirectory(base_path('public'));

        $this->info('Configs successfully updated');
    }
}
