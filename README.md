# Codeigniter DomPDF

The CodeIgniter Dompdf Library is a wrapper that assists in generating PDFs using the Dompdf library within the CodeIgniter framework.

The Dompdf library is a popular PHP library that allows you to create PDF documents from HTML markup. It converts HTML and CSS into PDF format, allowing you to generate PDF files dynamically based on your web application's data.

Using the CodeIgniter Dompdf Library, you can easily generate PDFs by passing HTML content or views to the library's methods. The library takes care of rendering the HTML, applying CSS styles, and converting it into a downloadable PDF file.

![PHP](https://img.shields.io/badge/PHP-%5E8.1-blue)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-%5E4.3-blue)

## Installation

Installation is done through Composer.

```console
composer require ictsolutions/codeigniter-dompdf
```

## Configuration

The library's default behavior can be overridden or augmented by its config file. Copy `examples/Pdf.php` to `app/Config/Pdf.php` and follow the instructions in the comments. If no config file is found the library will use its default. In the Breadcrumb class, you can set the following configuration options:

`showWarnings` (boolean, default: false)

If set to `true`, an Exception will be thrown on warnings from dompdf.

`convertEntities` (boolean, default: true)

If set to `true`, Dejavu Sans font is used. However, this font does not have glyphs for certain entities like € and £. If you need to display these characters correctly, you can set this option to `false`.

`defaultMediaType` (string, default: 'screen')

This option specifies the target media view which should be rendered into the PDF. Possible values are:
- screen
- tty
- tv
- projection
- handheld
- print
- braille
- aural
- all

`defaultPaperSize` (string, default: 'a4')

This option specifies the default paper size for the PDF document. It accepts standard paper sizes like 'letter', 'legal', 'A4', etc.

`defaultPaperOrientation` (string, default: 'portrait')

This option specifies the default paper orientation for the PDF document. Possible values are 'portrait' and 'landscape'.

`defaultFont` (string, default: 'serif')

This option specifies the default font family to be used if no suitable fonts can be found. The font must exist in the font folder.

`fontHeightRatio` (integer, default: 1)

This option applies a ratio to the font's height to make it more similar to browsers' line height.

`dpi` (integer, default: 96)

This option specifies the DPI (dots per inch) setting for images and fonts. This determines the default rendering resolution for the PDF document.

`isPhpEnabled` (boolean, default: false)

If set to `true`, DOMPDF will automatically evaluate inline PHP code contained within `<script type="text/php"> ... </script>` tags.

Note: Enabling this option for untrusted documents can be a security risk.

`isRemoteEnabled` (boolean, default: true)

If set to `true`, DOMPDF will access remote sites for images and CSS files as required.

Note: Enabling this option can be a security risk, especially when combined with other options like `isPhpEnabled`.

`isJavascriptEnabled` (boolean, default: true)

If set to `true`, DOMPDF will automatically insert JavaScript code contained within `<script type="text/javascript"> ... </script>` tags.
