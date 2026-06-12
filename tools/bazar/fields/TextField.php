<?php

namespace YesWiki\Bazar\Field;

use Psr\Container\ContainerInterface;
use YesWiki\Core\Service\HtmlPurifierService;

/**
 * @Field({"texte"})
 */
class TextField extends BazarField
{
    protected const FIELD_PATTERN = 6;
    protected const FIELD_SUB_TYPE = 7;
    protected const FIELD_PLACEHOLDER = 15;

    protected const ALLOWED_SUB_TYPES = ['text', 'date', 'email', 'url', 'range', 'password', 'number', 'color'];
    protected $pattern;
    protected $subType;
    protected $placeholder;

    public function __construct(array $values, ContainerInterface $services)
    {
        parent::__construct($values, $services);

        $this->pattern = $values[self::FIELD_PATTERN];
        $this->subType = $values[self::FIELD_SUB_TYPE];

        if (!empty($this->subType) && in_array($this->subType, self::ALLOWED_SUB_TYPES)) {
            $this->type = $this->subType;
        } else {
            $this->type = 'text';
        }

        $this->placeholder = $values[self::FIELD_PLACEHOLDER];
        $this->maxChars ??= 255;

        if ('range' === $this->type) {
            $this->size = empty($this->size) ? 0 : $this->size;
            $this->maxChars = empty($this->maxChars) ? 100 : $this->maxChars;
        }
    }

    public function getValueStructure() // See BazarField::getValueStructure
    {
        if ('number' == $this->type || 'range' == $this->type) {
            return [$this->propertyName => ['_mode_' => 'single', '_type_' => 'number']];
        }

        return [$this->propertyName => ['_mode_' => 'single', '_type_' => 'string']];
    }

    public function formatValuesBeforeSave($entry)
    {
        if (empty($this->propertyName)) {
            return [];
        }
        $dirtyHtml = $this->getValue($entry);
        $cleanHTML = $this->getService(HtmlPurifierService::class)->cleanHTML($dirtyHtml);

        return [$this->propertyName => $cleanHTML];
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function getSubType()
    {
        return $this->subType;
    }

    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    // change return of this method to keep compatible with php 7.3 (mixed is not managed)
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return array_merge(
            parent::jsonSerialize(),
            [
                'maxChars' => $this->getMaxChars(),
                'size' => $this->getSize(),
                'subType' => $this->getSubType(),
                'pattern' => $this->getPattern(),
                'placeholder' => $this->getPlaceholder(),
            ]
        );
    }

    protected function renderInput($entry)
    {
        // Handling all subtypes (url, number) in the text.twig
        return $this->render('@bazar/inputs/'.('range' == $this->getType() ? 'range' : 'text').'.twig', [
            'value' => $this->getValue($entry),
        ]);
    }

    protected function renderStatic($entry)
    {
        $value = $this->getValue($entry);
        if ('0' !== $value && !$value) {
            return '';
        }

        if ('bf_titre' === $this->name) {
            return $this->render('@bazar/fields/title.twig', [
                'value' => $value,
            ]);
        }

        return $this->render('@bazar/fields/text.twig', [
            'value' => $value,
        ]);
    }
}
