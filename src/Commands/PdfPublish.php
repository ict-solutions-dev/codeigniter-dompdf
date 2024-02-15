<?php

declare(strict_types=1);

namespace IctSolutions\CodeIgniterDompdf\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Publisher\Publisher;
use Throwable;

class PdfPublish extends BaseCommand
{
    protected $group       = 'Pdf';
    protected $name        = 'pdf:publish';
    protected $description = 'Publish Pdf config file into the current application.';

    public function run(array $params): void
    {
        $source = service('autoloader')->getNamespace('IctSolutions\\CodeIgniterDompdf')[0];

        $publisher = new Publisher($source, APPPATH);

        try {
            $publisher->addPaths([
                'Config/Pdf.php',
            ])->merge(false);
        } catch (Throwable $e) {
            $this->showError($e);
            return;
        }

        foreach ($publisher->getPublished() as $file) {
            $contents = file_get_contents($file);
            $contents = str_replace('namespace IctSolutions\\CodeIgniterDompdf\\Config', 'namespace Config', $contents);
            $contents = str_replace('use CodeIgniter\\Config\\BaseConfig', 'use IctSolutions\\CodeIgniterDompdf\\Config\\Pdf as BasePdf', $contents);
            $contents = str_replace('class Pdf extends BaseConfig', 'class Pdf extends BasePdf', $contents);
            file_put_contents($file, $contents);
        }

        CLI::write(CLI::color('  Published! ', 'green') . 'You can customize the configuration by editing the "app/Config/Pdf.php" file.');
    }
}
