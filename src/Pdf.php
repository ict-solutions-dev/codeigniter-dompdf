<?php

declare(strict_types=1);

namespace IctSolutions\CodeIgniterDompdf;

use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Dompdf\Adapter\CPDF;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use IctSolutions\CodeIgniterDompdf\Config\Pdf as PdfConfig;
use RuntimeException;

class Pdf
{
    private const MIME_TYPE = 'application/pdf';

    /**
     * The response object.
     */
    protected ResponseInterface $response;

    /**
     * The Dompdf instance.
     */
    protected Dompdf $dompdf;

    /**
     * Flag indicating whether to show warnings or not.
     */
    protected bool $showWarnings;

    /**
     * Flag indicating whether the rendering has been done or not.
     */
    protected bool $rendered = false;

    /**
     * Initializes the class and sets up the necessary configuration options for PDF generation.
     */
    public function __construct()
    {
        /** @var PdfConfig $config */
        $config = config('Pdf');

        $options = new Options();

        $options->set('tempDir', sys_get_temp_dir());
        $options->set('fontDir', WRITEPATH . 'fonts/');
        $options->set('fontCache', WRITEPATH . 'fonts/');
        $options->set('defaultMediaType', $config->defaultMediaType);
        $options->set('defaultPaperSize', $config->defaultPaperSize);
        $options->set('defaultPaperOrientation', $config->defaultPaperOrientation);
        $options->set('defaultFont', $config->defaultFont);
        $options->set('dpi', $config->dpi);
        $options->set('fontHeightRatio', $config->fontHeightRatio);
        $options->set('isPhpEnabled', $config->isPhpEnabled);
        $options->set('isRemoteEnabled', $config->isRemoteEnabled);
        $options->set('isJavascriptEnabled', $config->isJavascriptEnabled);

        $this->response     = Services::response();
        $this->dompdf       = new Dompdf($options);
        $this->showWarnings = $config->showWarnings;
    }

    /**
     * Get the DomPDF instance
     */
    public function getDomPDF(): Dompdf
    {
        return $this->dompdf;
    }

    /**
     * Show or hide warnings.
     *
     * @param bool $warnings Whether to show or hide warnings.
     */
    public function setWarnings(bool $warnings): self
    {
        $this->showWarnings = $warnings;

        return $this;
    }

    /**
     * Load HTML content into the Dompdf instance.
     *
     * @param string      $html     The HTML content to load.
     * @param string|null $encoding The character encoding of the HTML content.
     */
    public function loadHtml(string $html, ?string $encoding = null): self
    {
        $html = $this->convertEntities($html);

        $this->dompdf->loadHtml($html, $encoding);
        $this->rendered = false;

        return $this;
    }

    /**
     * Load an HTML file into the Dompdf instance.
     *
     * @param string $file The path to the HTML file to load.
     */
    public function loadFile(string $file): self
    {
        $this->dompdf->loadHtmlFile($file);
        $this->rendered = false;

        return $this;
    }

    /**
     * Load a view and convert it to HTML.
     *
     * @param string      $name     The name of the view to load.
     * @param array       $data     The data to pass to the view (default: []).
     * @param string|null $encoding The encoding to use for the HTML (default: null).
     */
    public function loadView(string $name, array $data = [], ?string $encoding = null): self
    {
        $html = view($name, $data);

        return $this->loadHTML($html, $encoding);
    }

    /**
     * Set an option for the Dompdf instance.
     *
     * @param array|string $attribute The name of the option to set.
     * @param mixed        $value     The value to set for the specified option.
     */
    public function setOption(string|array $attribute, mixed $value = null): self
    {
        $this->dompdf->getOptions()->set($attribute, $value);

        return $this;
    }

    /**
     * Returns the PDF as a string.
     *
     * The options parameter controls the output. Accepted options are:
     *
     * 'compress' = > 1 or 0 - apply content stream compression, this is
     *    on (1) by default
     *
     * @param array $options options (see above)
     */
    public function output(array $options = []): ?string
    {
        if (! $this->rendered) {
            $this->render();
        }

        return $this->dompdf->output($options);
    }

    /**
     * Downloads the PDF document.
     *
     * @param string $filename The name of the downloaded file (default: 'document.pdf').
     */
    public function download(string $filename = 'document.pdf'): ResponseInterface
    {
        $output = $this->output() ?? '';

        return $this->response
            ->setHeader('Content-Type', self::MIME_TYPE)
            ->setHeader('Content-disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Content-Length', (string) strlen($output))
            ->setStatusCode(200)
            ->setBody($output);
    }

    /**
     * Return a response with the PDF to show in the browser.
     *
     * @param string $filename The filename of the PDF (optional, defaults to 'document.pdf')
     */
    public function stream(string $filename = 'document.pdf'): ResponseInterface
    {
        $output = $this->output() ?? '';

        return $this->response
            ->setHeader('Content-Type', self::MIME_TYPE)
            ->setHeader('Content-disposition', 'inline; filename="' . $filename . '"')
            ->setStatusCode(200)
            ->setBody($output);
    }

    /**
     * Render the PDF document.
     *
     * This method generates the PDF content and prepares it for output.
     * It should be called before attempting to save or display the PDF.
     */
    public function render(): void
    {
        $this->dompdf->render();

        // Check for warnings
        if ($this->showWarnings) {
            $_dompdf_warnings = $GLOBALS['_dompdf_warnings'] ?? [];

            if (! empty($_dompdf_warnings) && count($_dompdf_warnings)) {
                $warnings = '';

                foreach ($_dompdf_warnings as $msg) {
                    $warnings .= $msg . "\n";
                }

                // Check for additional warnings
                // $warnings .= $this->dompdf->get_canvas()->get_cpdf()->messages;

                if (! empty($warnings)) {
                    throw new Exception($warnings);
                }
            }
        }

        $this->rendered = true;
    }

    /**
     * Sets encryption for the PDF document.
     *
     * @param string $password      The password to encrypt the document.
     * @param string $ownerPassword The owner password to encrypt the document. (optional)
     * @param array  $pc            An array of permission constants for the document. (optional)
     */
    public function setEncryption(string $password, string $ownerPassword = '', array $pc = []): void
    {
        $this->render();

        $canvas = $this->dompdf->getCanvas();

        if (! $canvas instanceof CPDF) {
            throw new RuntimeException('Encryption is only supported when using CPDF');
        }

        $canvas->get_cpdf()->setEncryption($password, $ownerPassword, $pc);
    }

    /**
     * Sets the footer for the PDF document.
     *
     * @param string|null $textBeforePaging The text to display before the page number. (optional)
     * @param string      $separator        The separator to use between the text and the page number. (default: '-')
     */
    public function setFooter(?string $textBeforePaging, string $separator = '-'): void
    {
        $this->render();

        $canvas = $this->dompdf->getCanvas();

        $x     = 500;
        $y     = 820;
        $size  = 7;
        $text  = "{$textBeforePaging} {$separator} {PAGE_NUM}/{PAGE_COUNT}";
        $font  = '';
        $color = [0, 0, 0];

        $canvas->page_text($x, $y, $text, $font, $size, $color);
    }

    /**
     * Convert special characters to HTML entities in the given subject.
     *
     * @param string $subject The subject string to convert entities in.
     */
    protected function convertEntities(string $subject): string
    {
        /** @var PdfConfig $config */
        $config = config('Pdf');

        if (false === $config->convertEntities) {
            return $subject;
        }

        $entities = [
            '€' => '&euro;',
            '£' => '&pound;',
        ];

        foreach ($entities as $search => $replace) {
            $subject = str_replace($search, $replace, $subject);
        }

        return $subject;
    }
}
