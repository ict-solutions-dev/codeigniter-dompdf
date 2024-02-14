<?php

declare(strict_types=1);

namespace IctSolutions\Admin\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use IctSolutions\CodeIgniterDompdf\Pdf;

class ExampleController extends Controller
{

    public function pdfStream(): ResponseInterface
    {
        helper('misc');

        $pdf = new Pdf();

        $data = [
            'example' => (object) ([
                'exampleTitle'    => 'Example title.',
                'exampleText'    => 'Example text.',
            ]),
        ];

        $pdf->loadView('example_view', $data);

        return $pdf->stream("example.pdf");
    }
}
