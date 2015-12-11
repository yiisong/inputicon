<?php

/**
 * @copyright  Copyright &copy; Andy Song <c3306@qq.com>, 2015
 * @package    yii2-widgets
 * @subpackage yii2-widget-iconinput
 * @version    1.0.0
 */


namespace common\components\iconinput;

/**
 * IconInput widget is an enhanced widget.
 *
 * @author Andy Song <c3306@qq.com>
 * @since  1.0
 */
class IconInputAsset extends \yii\web\AssetBundle
{
    public function init()
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('css', ['css/iconinput']);
        $this->setupAssets('js', ['js/iconinput']);
        parent::init();
    }
}
