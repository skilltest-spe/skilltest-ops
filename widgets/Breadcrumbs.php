<?php

namespace app\widgets;

use Yii;
use yii\base\Widget;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class Breadcrumbs extends Widget
{
    public $tag = 'ul';
    public $options = ['class' => 'breadcrumb'];
    public $encodeLabels = true;
    public $homeLink;
    public $links = [];
    public $itemTemplate = "<li class=\"breadcrumb-item\">{link}</li>\n";
    public $activeItemTemplate = "<li class=\"breadcrumb-item active\">{link}</li>\n";

    public function run()
    {
		if (empty($this->links)) {
            return;
        }
        
        $links = [];
        
        if ($this->homeLink === null) {
            $links[] = $this->renderItem([
				'encode'=>false,
                'label' => '<span class="fa fa-home"></span>',
                'url' => Yii::$app->homeUrl,
            ], $this->itemTemplate);
        } elseif ($this->homeLink !== false) {
            $links[] = $this->renderItem($this->homeLink, $this->itemTemplate);
        }

        foreach ($this->links as $link) {
            if (!is_array($link)) {
                $link = ['label' => $link];
            }
            $links[] = $this->renderItem($link, isset($link['url']) ? $this->itemTemplate : $this->activeItemTemplate);
        }

        echo Html::tag($this->tag, implode('', $links), $this->options);
    }
    
    protected function renderItem($link, $template)
    {
        $encodeLabel = $encode = ArrayHelper::remove($link, 'encode', $this->encodeLabels);

        if (array_key_exists('label', $link)) {
            $label = $encodeLabel ? Html::encode($link['label']) : $link['label'];
        } else {
            throw new InvalidConfigException('The "label" element is required for each link.');
        }

        if (isset($link['template'])) {
            $template = $link['template'];
        }

        if (isset($link['url'])) {
            $options = $link;
            unset($options['template'], $options['label'], $options['url']);
            $link = Html::a($label, $link['url'], $options);
        } else {
            $link = $label;
        }

        return strtr($template, ['{link}' => $link]);
    }
}