<?php

/**
 * Utility class for generating HTML forms for given LiteRecord models.
 */
class ModelLiteForm
{
    /**
     * Generates an HTML form for a given model.
     *
     * @param LiteRecord $model The model object for which the form is being created.
     * @param string $action The action URL for the form submission. Defaults to current route if not provided.
     * @return void
     */
    public static function create(LiteRecord $model, $action = ''): void
    {
        $model_name = get_class($model);

        if (!$action) {
            $action = ltrim(Router::get('route'), '/');
        }

        echo '<form action="', PUBLIC_PATH.$action, '" method="post" id="', $model_name, '" class="scaffold">' , PHP_EOL;

        // Get the primary key
        $pk = $model_name::getPK();
        echo '<input id="data_', $pk, '" name=" data[', $pk, ']" class="id" value="', $model->$pk , '" type="hidden">' , PHP_EOL;

        // Get the fields
        $fields = $model_name::metadata()->getFields();

        // Remove the primary key field
        unset($fields[$pk]);

        foreach ($fields as $field => $meta) {

            $type = $meta['Type'];
            $alias = self::getFieldAlias($field);

            $formId = 'data_'.$field;
            $formName = 'data['.$field.']';
            $required = '';

            if (!$meta['Null']) {
                echo "<label for=\"$formId\" class=\"required\">$alias *</label>" , PHP_EOL;
                $required = 'required';
            } else {
                echo "<label for=\"$formId\">$alias</label>" , PHP_EOL;
            }

            switch ($type) {
                case 'tinyint': case 'smallint': case 'mediumint':
                case 'integer': case 'int': case 'bigint':
                case 'float': case 'double': case 'precision':
                case 'real': case 'decimal': case 'numeric':
                case 'year': case 'day': case 'int unsigned': // Números

                if (str_ends_with($field, '_id')) {
                    echo Form::dbSelect($model_name.'.'.$field, null, null, 'Seleccione', '', $model->$field);
                    break;
                }

                echo "<input id=\"$formId\" type=\"number\" name=\"$formName\" value=\"{$model->$field}\" $required>" , PHP_EOL;
                break;

                case 'date':
                    echo "<input id=\"$formId\" type=\"date\" name=\"$formName\" value=\"{$model->$field}\" $required>" , PHP_EOL;
                    break;

                case 'datetime': case 'timestamp':
                echo "<input id=\"$formId\" type=\"datetime-local\" name=\"$formName\" value=\"{$model->$field}\" $required>" , PHP_EOL;
                break;

                case 'enum': case 'set': case 'bool':
                $enumList = explode(',', str_replace("'", '', substr($model->_data_type[$field], 5, (strlen($model->_data_type[$field]) - 6))));
                echo "<select id=\"$formId\" name=\"$formName\" $required>", PHP_EOL;
                foreach ($enumList as $value) {
                    echo "<option value=\"{$value}\">$value</option>", PHP_EOL;
                }
                echo '</select>', PHP_EOL;
                break;

                case 'text': case 'mediumtext': case 'longtext': // Usar textarea
                case 'blob': case 'mediumblob': case 'longblob':
                echo "<textarea id=\"$formId\" name=\"$formName\" $required>{$model->$field}</textarea>" , PHP_EOL;
                break;

                default: //text,tinytext,varchar, char,etc se comprobara su tamaño
                    echo "<input id=\"$formId\" type=\"text\" name=\"$formName\" value=\"{$model->$field}\" $required>" , PHP_EOL;
            }
        }
        echo '<input type="submit" value="Enviar" />' , PHP_EOL;
        echo '</form>' , PHP_EOL;
    }

    /**
     * Converts a field name to a human-readable alias.
     *
     * @param string $name The field name to convert.
     * @return string The human-readable alias for the field.
     */
    private static function getFieldAlias(string $name): string
    {
        return \ucwords(\str_replace('_', ' ', $name));
    }
}
