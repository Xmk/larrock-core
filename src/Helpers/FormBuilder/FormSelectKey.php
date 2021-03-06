<?php

namespace Larrock\Core\Helpers\FormBuilder;

use View;
use Illuminate\Database\Eloquent\Model;
use Larrock\Core\Exceptions\LarrockFormBuilderRowException;

/**
 * Select где в value следует ключ (он и сохраняется), а на выводе значение из массива options
 * Class FormSelectKey.
 */
class FormSelectKey extends FBElement
{
    /** @var null|mixed */
    public $options;

    /** @var string */
    public $option_title;

    /** @var string */
    public $option_key;

    /** @var null|mixed */
    public $connect;

    /** @var string Имя шаблона FormBuilder для отрисовки поля */
    public $FBTemplate = 'larrock::admin.formbuilder.select.key';

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param $row
     * @return $this
     */
    public function setOptionsTitle($row)
    {
        $this->option_title = $row;

        return $this;
    }

    /**
     * @param $row
     * @return $this
     */
    public function setOptionsKey($row)
    {
        $this->option_key = $row;

        return $this;
    }

    /**
     * Установка связи поля с какой-либо моделью
     * Сейчас применяется в FormSelect, FormCategory.
     * @param Model $model
     * @param null $relation_name
     * @param null $group_by
     * @return $this
     */
    public function setConnect($model, $relation_name = null, $group_by = null)
    {
        $this->connect = collect();
        $this->connect->model = $model;
        $this->connect->relation_name = $relation_name;
        $this->connect->group_by = $group_by;

        return $this;
    }

    /**
     * Установка опции выборки значений для setConnect().
     * @param string $key
     * @param string $value
     * @return $this
     * @throws LarrockFormBuilderRowException
     */
    public function setWhereConnect(string $key, string $value)
    {
        if (! isset($this->connect->model)) {
            throw new LarrockFormBuilderRowException('У поля '.$this->name.' сначала нужно определить setConnect');
        }
        $this->connect->where_key = $key;
        $this->connect->where_value = $value;

        return $this;
    }

    /**
     * Получени списка опций
     * @return \Illuminate\Support\Collection|mixed|null
     */
    public function getOptions()
    {
        if ($this->connect) {
            if (! $this->options) {
                $this->options = collect();
            } else {
                $this->options = collect($this->options);
            }
            $model = new $this->connect->model;
            $get_options_query = $model;
            if (isset($this->connect->where_key) && $this->connect->where_key) {
                $get_options_query = $get_options_query->where($this->connect->where_key, '=', $this->connect->where_value);
            }

            if (isset($this->connect->group_by) && $this->connect->group_by) {
                $get_options_query = $get_options_query->whereNotNull($this->connect->group_by)->groupBy([$this->connect->group_by]);
            }

            if ($get_options = $get_options_query->get()) {
                foreach ($get_options as $get_options_value) {
                    if ($get_options_value !== null) {
                        if ($this->option_key) {
                            $this->options->put($get_options_value->{$this->option_key}, $get_options_value->{$this->option_title});
                        } else {
                            $this->options->push($get_options_value);
                        }
                    }
                }
            }

            if (!$this->option_key) {
                $this->options = $this->options->pluck($this->name);
            }
        } else {
            $this->options = collect($this->options);
        }

        return $this->options->unique();
    }

    /**
     * Отрисовка элемента формы.
     * @return string
     */
    public function __toString()
    {
        if ($this->data && ! isset($this->data->{$this->name}) && $this->default) {
            $this->data->{$this->name} = $this->default;
        }

        $this->options = $this->getOptions();

        $selected = [];
        if (\Request::input($this->name)) {
            $selected[] = \Request::input($this->name);
        } elseif ($this->data) {
            $selected[] = $this->data->{$this->name};
        }

        return View::make($this->FBTemplate, ['row_key' => $this->name,
            'row_settings' => $this, 'data' => $this->data, 'selected' => $selected, ])->render();
    }
}
