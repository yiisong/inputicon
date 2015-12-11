<?php

/**
 * @copyright  Copyright &copy; Andy Song <c3306@qq.com>, 2015
 * @package    yii2-widgets
 * @subpackage yii2-widget-iconinput
 * @version    1.0.0
 */

namespace common\components\iconinput;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use yii\web\JsExpression;

/**
 * IconInput widget is an enhanced widget.
 *
 * @author Andy Song <c3306@qq.com>
 * @since  1.0
 * @see    http://git.i3a.com.cn
 */
class IconInput extends \yii\base\Widget
{
    const TYPE_INPUT = 1;
    const TYPE_COMPONENT_PREPEND = 2;
    const TYPE_COMPONENT_APPEND = 3;
    const TYPE_INLINE = 4;

    /**
     * @var string the markup type of widget markup
     * must be one of the TYPE constants. Defaults
     * to [[TYPE_COMPONENT_PREPEND]]
     */
    public $type = self::TYPE_COMPONENT_PREPEND;

    /**
     * @var string The size of the input - 'lg', 'md', 'sm', 'xs'
     */
    public $size;

    /**
     * @var ActiveForm the ActiveForm object which you can pass for seamless usage
     * with ActiveForm. This property is especially useful for client validation of
     * attribute2 for [[TYPE_RANGE]] validation
     */
    public $form;

    /**
     * @var array the HTML attributes for the button that is rendered for [[IconInput::TYPE_BUTTON]].
     * Defaults to `['class'=>'btn btn-default']`. The following special options are recognized:
     * - 'label': string the button label. Defaults to `<i class="glyphicon glyphicon-calendar"></i>`
     */
    public $buttonOptions = [];

    /**
     * @var mixed the calendar picker button configuration.
     * - if this is passed as a string, it will be displayed as is (will not be HTML encoded).
     * - if this is set to false, the picker button will not be displayed.
     * - if this is passed as an array (this is the DEFAULT) it will treat this as HTML attributes
     *   for the button (to be displayed as a Bootstrap addon). The following special keys are recognized;
     *   - icon - string, the bootstrap glyphicon name/suffix. Defaults to 'calendar'.
     *   - title - string|bool, the title to be displayed on hover. Defaults to 'Select date & time'. To disable,
     *     set it to `false`.
     */
    public $pickerButton = [];

    /**
     * @var mixed the calendar remove button configuration - applicable only for type
     * set to `IconInput::TYPE_COMPONENT_PREPEND` or `IconInput::TYPE_COMPONENT_APPEND`.
     * - if this is passed as a string, it will be displayed as is (will not be HTML encoded).
     * - if this is set to false, the remove button will not be displayed.
     * - if this is passed as an array (this is the DEFAULT) it will treat this as HTML attributes
     *   for the button (to be displayed as a Bootstrap addon). The following special keys are recognized;
     *   - icon - string, the bootstrap glyphicon name/suffix. Defaults to 'remove'.
     *   - title - string, the title to be displayed on hover. Defaults to 'Clear field'. To disable,
     *     set it to `false`.
     */
    public $removeButton = [];

    /**
     * @var array the HTML attributes for the input tag.
     */
    public $options = [];

    /**
     * @var array The addon that will be prepended/appended for a  [[TYPE_COMPONENT_PREPEND]] and
     * [[TYPE_COMPONENT_APPEND]]. You can set the following array keys:
     * - part1: string, the content to prepend before the [[TYPE_COMPONENT_PREPEND]] OR
     *          before input # 1 for [[TYPE_RANGE]].
     * - part2: string, the content to prepend after the [[TYPE_COMPONENT_PREPEND]]  OR
     *          after input # 1 for [[TYPE_RANGE]].
     * - part3: string, the content to append before the [[TYPE_COMPONENT_APPEND]]  OR
     *          before input # 2 for [[TYPE_RANGE]].
     * - part4: string, the content to append after the [[TYPE_COMPONENT_APPEND]] OR
     *          after input # 2 for [[TYPE_RANGE]].
     */
    public $addon = [];

   /**
     * @var array the HTML options for the IconInput container
     */
    private $_container = [];

    /**
     * @var bool whether a prepend or append addon exists
     */
    protected $_hasAddon = false;

    /**
     * Initializes the widget
     * @throws
     * @throws InvalidConfigException
     */
    public function init()
    {
        $this->_msgCat = 'kvicon';
        $this->pluginName = 'kvIconInput';
        parent::init();
        $this->_hasAddon = $this->type == self::TYPE_COMPONENT_PREPEND || $this->type == self::TYPE_COMPONENT_APPEND;

        if ($this->type < 1 || $this->type > 4 || !is_int($this->type)) {
            throw new InvalidConfigException("Invalid value for the property 'type'. Must be an integer between 1 and 6.");
        }
        if (isset($this->form) && !($this->form instanceof \yii\widgets\ActiveForm)) {
            throw new InvalidConfigException("The 'form' property must be of type \\yii\\widgets\\ActiveForm");
        }
        if (isset($this->form) && !$this->hasModel()) {
            throw new InvalidConfigException("You must set the 'model' and 'attribute' properties when the 'form' property is set.");
        }
        if (isset($this->addon) && !is_array($this->addon)) {
            throw new InvalidConfigException("The 'addon' property must be setup as an array with 'part1', 'part2', 'part3', and/or 'part4' keys.");
        }
        $this->options['id'] .= time();
        $this->_container['id'] = $this->options['id'] . '-' . $this->_msgCat;
        $this->registerAssets();
        echo $this->renderInput();
    }

    /**
     * Renders the source input for the IconInput plugin.
     * Graceful fallback to a normal HTML  text input - in
     * case JQuery is not supported by the browser
     */
    protected function renderInput()
    {
        if ($this->type == self::TYPE_INLINE) {
            if (empty($this->options['readonly'])) {
                $this->options['readonly'] = true;
            }
            if (empty($this->options['class'])) {
                $this->options['class'] = 'form-control input-sm text-center';
            }
        } else {
            Html::addCssClass($this->options, 'form-control');
        }

        if (isset($this->form)) {
            $vars = call_user_func('get_object_vars', $this);
            unset($vars['form']);
            return $this->form->field($this->model, $this->attribute)->widget(self::classname(), $vars);
        }
        $input = 'textInput';
        return $this->parseMarkup($this->getInput($input));
    }

    /**
     * Returns the addon to render
     *
     * @param array $options the HTML attributes for the addon
     * @param string $type whether the addon is the picker or remove
     * @return string
     */
    protected function renderAddon(&$options, $type = 'picker')
    {
        if ($options === false) {
            return '';
        }
        if (is_string($options)) {
            return $options;
        }
        $icon = ($type === 'picker') ? 'calendar' : 'remove';
        Html::addCssClass($options, 'input-group-addon kv-icon-' . $icon . ' iconinput-action-' . $type);
        $icon = '<i class="glyphicon glyphicon-' . ArrayHelper::remove($options, 'icon', $icon) . '"></i>';
        $title = ArrayHelper::getValue($options, 'title', '');
        if ($title !== false && empty($title)) {
            $options['title'] = ($type === 'picker') ? '选择图标' : '清除图标';
        }
        return Html::tag('span', $icon, $options);
    }

    /**
     * Parses the input to render based on markup type
     *
     * @param string $input
     * @return string
     */
    protected function parseMarkup($input)
    {
        $css = $this->disabled ? ' disabled' : '';
        if ($this->type == self::TYPE_INPUT || $this->type == self::TYPE_INLINE) {
            if (isset($this->size)) {
                Html::addCssClass($this->options, 'input-' . $this->size . $css);
            }
        } elseif (isset($this->size)) {
            Html::addCssClass($this->_container, 'input-group input-group-' . $this->size . $css);
        }
        Html::addCssClass($this->_container, 'input-group' . $css);
        if ($this->type == self::TYPE_INPUT) {
            return $input;
        }
        $part1 = $part2 = $part3 = $part4 = '';
        if (!empty($this->addon) && $this->_hasAddon) {
            $part1 = ArrayHelper::getValue($this->addon, 'part1', '');
            $part2 = ArrayHelper::getValue($this->addon, 'part2', '');
            $part3 = ArrayHelper::getValue($this->addon, 'part3', '');
            $part4 = ArrayHelper::getValue($this->addon, 'part4', '');
        }
        if ($this->_hasAddon) {
            Html::addCssClass($this->_container, 'icon');
            $picker = $this->renderAddon($this->pickerButton);
            $remove = $this->renderAddon($this->removeButton, 'remove');
            if ($this->type == self::TYPE_COMPONENT_APPEND) {
                $content = $part1 . $part2 . $input . $part3 . $remove . $picker . $part4;
            } else {
                $content = $part1 . $picker . $remove . $part2 . $input . $part3 . $part4;
            }
            return Html::tag('div', $content, $this->_container);
        }
        if ($this->type == self::TYPE_INLINE) {
            return Html::tag('div', '', $this->_container) . $input;
        }
    }

    /**
     * Registers the needed client assets
     */
    public function registerAssets()
    {
        if ($this->disabled) {
            return;
        }
        $view = $this->getView();
        IconInputAsset::register($view);

        $id = "jQuery('#" . $this->options['id'] . "')";
        $this->registerPlugin($this->pluginName, $id);
        if ($this->type === self::TYPE_INLINE) {
            $view->registerJs("{$id}.on('changeDate',function(e){{$id}.val(e.format()).trigger('change')});");
        }
    }
}