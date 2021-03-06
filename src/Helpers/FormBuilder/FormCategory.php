<?php

namespace Larrock\Core\Helpers\FormBuilder;

use View;
use LarrockCategory;
use Larrock\Core\Helpers\Tree;
use Illuminate\Database\Eloquent\Model;
use Larrock\Core\Exceptions\LarrockFormBuilderRowException;

class FormCategory extends FBElement
{
    /** @var mixed */
    public $options;

    /** @var null|int */
    public $max_items;

    /** @var null|bool */
    public $allow_empty;

    /** @var null|mixed */
    public $connect;

    /** @var string Имя шаблона FormBuilder для отрисовки поля */
    public $FBTemplate = 'larrock::admin.formbuilder.tags.categoryTree';

    /**
     * @param null|int $max
     * @return $this
     */
    public function setMaxItems($max = null)
    {
        $this->max_items = $max;

        return $this;
    }

    /**
     * @return $this
     */
    public function setAllowEmpty()
    {
        $this->allow_empty = true;

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
     * Отрисовка элемента формы.
     * @return string
     */
    public function __toString()
    {
        if (! isset($this->connect->model, $this->connect->relation_name)) {
            return 'Отрисовка не возможна! Поля model, relation_name не установлены через setConnect()';
        }

        if ($this->data && ! isset($this->data->{$this->name}) && $this->default) {
            $this->data->{$this->name} = $this->default;
        }

        $this->options = collect();
        /** @var \Eloquent $model */
        $model = new $this->connect->model;
        if (isset($this->connect->where_key)) {
            $get_options = $model::where($this->connect->where_key, '=', $this->connect->where_value)->get(['id', 'parent', 'level', 'title']);
        } else {
            $get_options = $model::get(['id', 'parent', 'level', 'title']);
        }
        if ($get_options) {
            foreach ($get_options as $get_options_value) {
                $this->options->push($get_options_value);
            }
        }

        $selected = null;
        if ($this->data) {
            $selected = $this->data->{$this->connect->relation_name};
            if (\count($selected) === 1 && isset($selected->id)) {
                $once_category[] = $selected;
                $selected = $once_category;
            }
        }

        if ($selected === null
            && isset($this->data->{$this->name})
            && ($get_category = LarrockCategory::getModel()->whereId($this->data->{$this->name})->first())) {
            $selected[] = $get_category;
        }

        $tree = new Tree;
        $this->options = $tree->buildTree($this->options, 'parent');

        return View::make($this->FBTemplate, ['row_key' => $this->name,
            'row_settings' => $this, 'data' => $this->data, 'selected' => $selected, ])->render();
    }
}
