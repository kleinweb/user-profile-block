<?php

// SPDX-FileCopyrightText: 2025 Temple University <kleinweb@temple.edu>
//
// SPDX-License-Identifier: GPL-3.0-or-later

declare(strict_types=1);

namespace Kleinweb\UserProfile\Meta\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final readonly class Meta
{
    /**
     * @param string                         $key              The meta key
     * @param 'post'|'term'|'user'|'comment' $objectType       The object type this meta belongs to
     * @param string|null                    $objectSubtype    Post type, taxonomy, or null for all
     * @param string                         $type             Data type: string, integer, number, boolean, array, object
     * @param string                         $label            Human-readable label for UI
     * @param string                         $description      Description for UI/REST schema
     * @param mixed                          $default          Default value
     * @param bool                           $single           Whether this is single meta (true) or array meta (false)
     * @param bool                           $showInRest       Whether to expose in REST API
     * @param bool                           $showInEditor     Whether to show in block editor sidebar
     * @param string|null                    $inputType        UI input type: text, textarea, number, checkbox, select, date, etc
     * @param array<string, string>|null     $options          Options for select inputs
     * @param string|null                    $sanitizeCallback Custom sanitization callback
     * @param string|null                    $authCallback     Custom authorization callback
     */
    public function __construct(
        public string $key,
        public string $objectType = 'post',
        public ?string $objectSubtype = null,
        public string $type = 'string',
        public string $label = '',
        public string $description = '',
        public mixed $default = null,
        public bool $single = true,
        public bool $showInRest = true,
        public bool $showInEditor = true,
        public ?string $inputType = null,
        public ?array $options = null,
        public ?string $sanitizeCallback = null,
        public ?string $authCallback = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArgs(): array
    {
        $args = [
            'type' => $this->type,
            'description' => $this->description,
            'single' => $this->single,
            'default' => $this->default ?? $this->getDefaultForType(),
            'show_in_rest' => $this->showInRest ? $this->getRestSchema() : false,
        ];

        if ($this->objectSubtype) {
            $args['object_subtype'] = $this->objectSubtype;
        }

        if ($this->sanitizeCallback) {
            $args['sanitize_callback'] = $this->sanitizeCallback;
        }

        if ($this->authCallback) {
            $args['auth_callback'] = $this->authCallback;
        }

        return $args;
    }

    /**
     * @return array<string, mixed>
     */
    public function getUiConfig(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label ?: $this->key,
            'description' => $this->description,
            'type' => $this->type,
            'inputType' => $this->inputType ?? $this->inferInputType(),
            'options' => $this->options,
            'default' => $this->default ?? $this->getDefaultForType(),
        ];
    }

    /**
     * @return array{schema: array<string, mixed>}
     */
    private function getRestSchema(): array
    {
        $schema = [
            'type' => $this->type,
            'description' => $this->description,
        ];

        if ($this->default !== null) {
            $schema['default'] = $this->default;
        }

        return ['schema' => $schema];
    }

    private function getDefaultForType(): mixed
    {
        return match ($this->type) {
            'string' => '',
            'integer', 'number' => 0,
            'boolean' => false,
            'array' => [],
            'object' => (object) [],
            default => null,
        };
    }

    private function inferInputType(): string
    {
        if ($this->options !== null) {
            return 'select';
        }

        return match ($this->type) {
            'boolean' => 'checkbox',
            'integer', 'number' => 'number',
            default => 'text',
        };
    }
}
