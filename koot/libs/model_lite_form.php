<?php

/**
 * Utility class for generating HTML forms for given LiteRecord models.
 */
class ModelLiteForm
{
    /**
     * @var array List of number types that should be rendered as input fields.
     */
    protected static array $numberTypes = [
        'tinyint',
        'smallint',
        'mediumint',
        'int',
        'integer',
        'bigint',
        'float',
        'double',
        'precision',
        'real',
        'decimal',
        'numeric',
        'year',
        'day',
        'int unsigned',
    ];

    /**
     * @var array List of text types that should be rendered as textarea fields.
     */
    protected static array $textTypes = [
        'text',
        'mediumtext',
        'longtext',
        'blob',
        'mediumblob',
        'longblob',
    ];

    /**
     * @var array List of date types that should be rendered as input fields with type="date".
     */
    protected static array $dateTypes = ['date'];

    /**
     * @var array List of date and time types that should be rendered as input fields with type="datetime".
     */
    protected static array $dateTimeTypes = ['datetime', 'timestamp'];

    /**
     * Generates an HTML form for a given model.
     *
     * @param LiteRecord $model The model object for which the form is being created.
     * @param string $action The action URL for the form submission. Defaults to current route if not provided.
     * @return void
     */
    public static function create(LiteRecord $model, string $action = ''): void
    {
        $model_name = get_class($model);

        if ('' === $action) {
            $action = ltrim(Router::get('route'), '/');
        }

        // Get the primary key
        $pk = $model_name::getPK();
        $pkValue = htmlspecialchars(
            $model->$pk,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        echo '<form action="', PUBLIC_PATH, $action, '" method="post" id="', $model_name, '" class="scaffold">' . PHP_EOL;

        if ($pkValue) {
            echo '<input id="' , $model_name , '_' , $pk, '" name="' , $model_name , '[', $pk . ']" value="', $pkValue, '" type="hidden">' . PHP_EOL;
        }

        // Get the fields
        $fields = $model_name::metadata()->getFields();

        // Remove the primary key field
        unset($fields[$pk]);

        foreach ($fields as $field => $meta) {
            $type = $meta['Type'];
            $alias = self::getFieldAlias($field);

            $inputId = $model_name . '_' . $field;
            $inputName = $model_name . '[' . $field . ']';

            $isRequired = !$meta['Null'];
            $requiredAttr = $isRequired ? 'required' : '';
            $labelClass = $isRequired ? 'class="required"' : '';
            $asterisk = $isRequired ? ' *' : '';

            echo "<label {$labelClass}>{$alias}{$asterisk}" . PHP_EOL;

            $value = htmlspecialchars(
                $model->$field,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );

            if (str_ends_with($field, '_id')) {
                echo Form::dbSelect(
                    "{$model_name}.{$field}",
                    null,
                    null,
                    'Select',
                    '',
                    $model->$field
                );
                continue;
            }

            $inputHtml = self::getInput(
                $type,
                $value,
                $inputId,
                $inputName,
                $requiredAttr
            );
            echo $inputHtml, PHP_EOL;
            echo '</label>', PHP_EOL;
        }

        echo '<input type="submit" />', PHP_EOL;
        echo '</form>', PHP_EOL;
    }

    /**
     * Converts a field name to a human-readable alias.
     *
     * @param string $name The field name to convert.
     * @return string The human-readable alias for the field.
     */
    private static function getFieldAlias(string $name): string
    {
        return ucwords(str_replace('_', ' ', $name));
    }

    /**
     * Generates the appropriate input HTML based on the field type.
     *
     * @param string $type The database field type.
     * @param string $value The value of the field.
     * @param string $inputId The id attribute for the input element.
     * @param string $inputName The name attribute for the input element.
     * @param string $requiredAttr The required attribute for the input element.
     * @return string The HTML input element as a string.
     */
    private static function getInput(
        string $type,
        string $value,
        string $inputId,
        string $inputName,
        string $requiredAttr
    ): string
    {
        if (in_array($type, static::$numberTypes, true)) {
            $input = self::buildInputElement(
                'number',
                $inputId,
                $inputName,
                $value,
                $requiredAttr
            );
        } elseif (in_array($type, static::$dateTypes, true)) {
            $input = self::buildInputElement(
                'date',
                $inputId,
                $inputName,
                $value,
                $requiredAttr
            );
        } elseif (in_array($type, static::$dateTimeTypes, true)) {
            $input = self::buildInputElement(
                'datetime-local',
                $inputId,
                $inputName,
                $value,
                $requiredAttr
            );
        } elseif (self::isSelectTypeMatched($type)) {
            $input = self::buildSelectElement(
                $inputId,
                $inputName,
                self::getEnumOptions($type),
                $value,
                $requiredAttr
            );
        } elseif (in_array($type, static::$textTypes, true)) {
            $input = self::buildTextareaElement(
                $inputId,
                $inputName,
                $requiredAttr,
                $value
            );
        } else {
            $input = self::buildInputElement(
                'text',
                $inputId,
                $inputName,
                $value,
                $requiredAttr
            );
        }

        return $input;
    }

    /**
     * Determines if the given type string matches one of the specific types: 'enum', 'set', or 'bool'.
     *
     * @param string $type The type string to check.
     * @return bool True if the type matches 'enum', 'set', or 'bool', false otherwise.
     */
    private static function isSelectTypeMatched(string $type): bool
    {
        return str_starts_with($type, 'enum') ||
            str_starts_with($type, 'set') ||
            str_starts_with($type, 'bool');
    }

    /**
     * Builds an HTML input element.
     *
     * @param string $type The type attribute for input elements.
     * @param string $id The id attribute.
     * @param string $name The name attribute.
     * @param string $value The value attribute.
     * @param string $requiredAttr The required attribute.
     * @return string The HTML element as a string.
     */
    private static function buildInputElement(
        string $type,
        string $id,
        string $name,
        string $value = '',
        string $requiredAttr = ''
    ): string
    {
        $attributesString = self::attributesToString([
            'id' => $id,
            'name' => $name,
            'type' => $type,
            'value' => $value,
            $requiredAttr => $requiredAttr,
        ]);
        return "<input {$attributesString}>";
    }

    /**
     * Builds an HTML textarea element.
     *
     * @param string $id The id attribute.
     * @param string $name The name attribute.
     * @param string $requiredAttr The required attribute.
     * @param string $content The content for textarea elements.
     *
     * @return string The HTML element as a string.
     */
    private static function buildTextareaElement(
        string $id,
        string $name,
        string $requiredAttr = '',
        string $content = ''
    ): string
    {
        $attributesString = self::attributesToString([
            'id' => $id,
            'name' => $name,
            $requiredAttr => $requiredAttr,
        ]);
        return "<textarea {$attributesString}>{$content}</textarea>";
    }

    /**
     * Builds an HTML select element.
     *
     * @param string $id The id attribute.
     * @param string $name The name attribute.
     * @param array $options The options for the select.
     * @param string $selectedValue The selected value.
     * @param string $requiredAttr The required attribute.
     * @return string The HTML select element as a string.
     */
    private static function buildSelectElement(
        string $id,
        string $name,
        array  $options,
        string $selectedValue,
        string $requiredAttr
    ): string
    {
        $attributesString = self::attributesToString([
            'id' => $id,
            'name' => $name,
            $requiredAttr => $requiredAttr,
        ]);
        $selectHtml = "<select {$attributesString}>" . PHP_EOL;
        foreach ($options as $option) {
            $selected = $option === $selectedValue ? ' selected' : '';
            $optionEscaped = htmlspecialchars(
                $option,
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            );
            $selectHtml .=
                "<option value=\"{$optionEscaped}\"{$selected}>{$optionEscaped}</option>" .
                PHP_EOL;
        }
        $selectHtml .= '</select>';
        return $selectHtml;
    }

    /**
     * Converts an array of attributes into a string for HTML tags.
     *
     * @param array $attributes The attributes to convert.
     * @return string The attributes as a string.
     */
    private static function attributesToString(array $attributes): string
    {
        $attributePairs = array_map(
            function ($key, $value) {
                if ($value === '' || is_int($key)) {
                    return '';
                }
                $escapedValue = htmlspecialchars(
                    $value,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                );
                return "{$key}=\"{$escapedValue}\"";
            },
            array_keys($attributes),
            $attributes
        );

        return implode(' ', array_filter($attributePairs));
    }

    /**
     * Extracts the options from an enum or set type definition.
     *
     * @param string $typeDefinition The type definition string from the database.
     * @return array The list of options.
     */
    private static function getEnumOptions(string $typeDefinition): array
    {
        preg_match('/^(enum|set)\((.*)\)$/', $typeDefinition, $matches);
        if (!$matches) {
            return [];
        }

        return str_getcsv($matches[2], ',', "'");
    }
}
