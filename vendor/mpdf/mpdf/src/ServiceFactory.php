<?php

namespace Mpdf;

use Mpdf\Color\ColorConverter;
use Mpdf\Color\ColorModeConverter;
use Mpdf\Color\ColorSpaceRestrictor;

use Mpdf\Fonts\FontCache;
use Mpdf\Fonts\FontFileFinder;

use Mpdf\Image\ImageProcessor;

use Mpdf\Pdf\Protection;
use Mpdf\Pdf\Protection\UniqidGenerator;

use Mpdf\Writer\BaseWriter;
use Mpdf\Writer\BackgroundWriter;
use Mpdf\Writer\ColorWriter;
use Mpdf\Writer\BookmarkWriter;
use Mpdf\Writer\FontWriter;
use Mpdf\Writer\FormWriter;
use Mpdf\Writer\ImageWriter;
use Mpdf\Writer\JavaScriptWriter;
use Mpdf\Writer\MetadataWriter;
use Mpdf\Writer\OptionalContentWriter;
use Mpdf\Writer\PageWriter;

use Mpdf\Writer\ResourceWriter;
use Psr\Log\LoggerInterface;

class ServiceFactory
{

	public function getServices(
		Mpdf $mpdf,
		LoggerInterface $logger,
		$config,
		$restrictColorSpace,
		$languageToFont,
		$scriptToLanguage,
		$fontDescriptor,
		$bmp,
		$directWrite,
		$wmf
	) {
		$sizeConverter = new SizeConverter($mpdf->dpi, $mpdf->default_font_size, $mpdf, $logger);

		$colorModeConverter = new ColorModeConverter();
		$colorSpaceRestrictor = new ColorSpaceRestrictor(
			$mpdf,
			$colorModeConverter,
			$restrictColorSpace
		);
		$colorConverter = new ColorConverter($mpdf, $colorModeConverter, $colorSpaceRestrictor);

		$tableOfContents = new TableOfContents($mpdf, $sizeConverter);

		$cacheBasePath = $config['tempDir'] . '/mpdf';

		$cache = new Cache($cacheBasePath, $config['cacheCleanupInterval']);
		$fontCache = new FontCache(new Cache($cacheBasePath . '/ttfontdata', $config['cacheCleanupInterval']));

		$fontFileFinder = new FontFileFinder($config['fontDir']);

		$cssManager = new CssManager($mpdf, $cache, $sizeConverter, $colorConverter);

		$otl = new Otl($mpdf, $fontCache);

		$protection = new Protection(new UniqidGenerator());

		$writer = new BaseWriter($mpdf, $protection);

		$gradient = new Gradient($mpdf, $sizeConverter, $colorConverter, $writer);

		$formWriter = new FormWriter($mpdf, $writer);

		$form = new Form($mpdf, $otl, $colorConverter, $writer, $formWriter);

		$hyphenator = new Hyphenator($mpdf);

		$remoteContentFetcher = new RemoteContentFetcher($mpdf, $logger);

		$imageProcessor = new ImageProcessor(
			$mpdf,
			$otl,
			$cssManager,
			$sizeConverter,
			$colorConverter,
			$colorModeConverter,
			$cache,
			$languageToFont,
			$scriptToLanguage,
			$remoteContentFetcher,
			$logger
		);

		$tag = new Tag(
			$mpdf,
			$cache,
			$cssManager,
			$form,
			$otl,
			$tableOfContents,
			$sizeConverter,
			$colorConverter,
			$imageProcessor,
			$languageToFont
		);

		$fontWriter = new FontWriter($mpdf, $writer, $fontCache, $fontDescriptor);
		$metadataWriter = new MetadataWriter($mpdf, $writer, $form, $protection, $logger);
		$imageWriter = new ImageWriter($mpdf, $writer);
		$pageWriter = new PageWriter($mpdf, $form, $writer, $metadataWriter);
		$bookmarkWriter = new BookmarkWriter($mpdf, $writer);
		$optionalContentWriter = new OptionalContentWriter($mpdf, $writer);
		$colorWriter = new ColorWriter($mpdf, $writer);
		$backgroundWriter = new BackgroundWriter($mpdf, $writer);
		$javaScriptWriter = new JavaScriptWriter($mpdf, $writer);

		$resourceWriter = new ResourceWriter(
			$mpdf,
			$writer,
			$colorWriter,
			$fontWriter,
			$imageWriter,
			$formWriter,
			$optionalContentWriter,
			$backgroundWriter,
			$bookmarkWriter,
			$metadataWriter,
			$javaScriptWriter,
			$logger
		);

		return [
			'otl' => $otl,
			'bmp' => $bmp,
			'cache' => $cache,
			'cssManager' => $cssManager,
			'directWrite' => $directWrite,
			'fontCache' => $fontCache,
			'fontFileFinder' => $fontFileFinder,
			'form' => $form,
			'gradient' => $gradient,
			'tableOfContents' => $tableOfContents,
			'tag' => $tag,
			'wmf' => $wmf,
			'sizeConverter' => $sizeConverter,
			'colorConverter' => $colorConverter,
			'hyphenator' => $hyphenator,
			'remoteContentFetcher' => $remoteContentFetcher,
			'imageProcessor' => $imageProcessor,
			'protection' => $protection,

			'languageToFont' => $languageToFont,
			'scriptToLanguage' => $scriptToLanguage,

			'writer' => $writer,
			'fontWriter' => $fontWriter,
			'metadataWriter' => $metadataWriter,
			'imageWriter' => $imageWriter,
			'formWriter' => $formWriter,
			'pageWriter' => $pageWriter,
			'bookmarkWriter' => $bookmarkWriter,
			'optionalContentWriter' => $optionalContentWriter,
			'colorWriter' => $colorWriter,
			'backgroundWriter' => $backgroundWriter,
			'javaScriptWriter' => $javaScriptWriter,

			'resourceWriter' => $resourceWriter
		];
	}

}
