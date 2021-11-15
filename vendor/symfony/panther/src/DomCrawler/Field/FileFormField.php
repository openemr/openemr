<?php

/*
 * This file is part of the Panther project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Symfony\Component\Panther\DomCrawler\Field;

use Symfony\Component\DomCrawler\Field\FileFormField as BaseFileFormField;

/**
 * @author Robert Freigang <robertfreigang@gmx.de>
 */
final class FileFormField extends BaseFileFormField
{
    use FormFieldTrait;

    /**
     * @var array
     */
    protected $value;

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): void
    {
        $value = $this->sanitizeValue($value);

        if (null !== $value && \is_readable($value)) {
            $error = \UPLOAD_ERR_OK;
            $size = \filesize($value);
            $name = \pathinfo($value, \PATHINFO_BASENAME);

            $this->setFilePath($value);
            $value = $this->element->getAttribute('value');
        } else {
            $error = \UPLOAD_ERR_NO_FILE;
            $size = 0;
            $name = '';
            $value = '';
        }

        $this->value = ['name' => $name, 'type' => '', 'tmp_name' => $value, 'error' => $error, 'size' => $size];
    }

    /**
     * Sets path to the file as string for simulating HTTP request.
     *
     * @param string $path The path to the file
     */
    public function setFilePath($path): void
    {
        $this->element->sendKeys($this->sanitizeValue($path));
    }

    /**
     * Initializes the form field.
     *
     * @throws \LogicException When node type is incorrect
     */
    protected function initialize(): void
    {
        $tagName = $this->element->getTagName();
        if ('input' !== $tagName) {
            throw new \LogicException(\sprintf('A FileFormField can only be created from an input tag (%s given).', $tagName));
        }

        $type = \strtolower($this->element->getAttribute('type'));
        if ('file' !== $type) {
            throw new \LogicException(\sprintf('A FileFormField can only be created from an input tag with a type of file (given type is %s).', $type));
        }

        $value = $this->element->getAttribute('value');
        if ($value) {
            $this->setValueFromTmp($value);
        } else {
            $this->setValue(null);
        }
    }

    private function setValueFromTmp(string $tmpValue): void
    {
        $value = $tmpValue;
        $error = \UPLOAD_ERR_OK;
        // size not determinable
        $size = 0;
        // C:\fakepath\filename.extension
        $basename = \pathinfo($value, \PATHINFO_BASENAME);
        $nameParts = \explode('\\', $basename);
        $name = \end($nameParts);

        $this->value = ['name' => $name, 'type' => '', 'tmp_name' => $value, 'error' => $error, 'size' => $size];
    }

    private function sanitizeValue(?string $value): ?string
    {
        $realpathValue = \is_string($value) && $value ? \realpath($value) : false;
        if (\is_string($realpathValue)) {
            $value = $realpathValue;
        }

        return $value;
    }
}
