<?php

namespace ImageProxy\Admin;

class Page
{
    public static $slug = 'image-proxy-option';
    private $options = [];

    public function __construct()
    {
        $this->options = self::getOptions();
        add_action('admin_menu', [$this, 'subPage']);
    }

    /**
     * Получаем опции из базы
     * @return array
     */
    public static function getOptions()
    {
        return get_option(self::$slug, [
            'key' => '',
            'salt' => '',
            'host' => '',
            'active'=>0,
        ]);
    }

    public function subPage()
    {
        add_submenu_page(
            "options-general.php",
            __('Imgproxy settings', 'ImageProxy'),
            __('Imgproxy options', 'ImageProxy'),
            'edit_others_posts',
            self::$slug,
            [$this, 'PageContent']
        );

        $descriptionSection = $this->section('base', "Base Settings", "");

        $this->field(
            $descriptionSection,
            'key',
            [
                'label'=>__('Key','ImageProxy'),
                'tag' => 'input',
                'attrs' => [
                    'required' => 'required',
                    'type' => 'text'
                ]
            ]
        );

        $this->field(
            $descriptionSection,
            'salt',
            [
                'label'=>__('Salt','ImageProxy'),
                'tag' => 'input',
                'attrs' => [
                    'required' => 'required',
                    'type' => 'text'
                ]
            ]
        );

        $this->field(
            $descriptionSection,
            'host',
            [
                'label'=>__('Host','ImageProxy'),
                'tag' => 'input',
                'attrs' => [
                    'required' => 'required',
                    'type' => 'text'
                ]
            ]
        );


        $this->field(
            $descriptionSection,
            'active',
            [
                'label'=>__('Active','ImageProxy'),
                'tag' => 'select',
                'attrs' => [
                    'required' => 'required',
                ],
                'options' => [
                    0 => __('Off', 'ImageProxy'),
                    1 => __('On', 'ImageProxy'),
                ],
                'selected' => 0,
            ]
        );

        register_setting(self::$slug, self::$slug);
    }

    private function field($sectionPrefix, $name = '', $data = [], $html = false)
    {
        $value = $this->options;

        add_settings_field(
            self::$slug . "_{$name}_field",
            isset($data['label']) ? $data['label'] : $name,
            function () use ($html, $value, $name, $data) {
                $data['value'] = (isset($value[$name]) ? $value[$name] : '');
                if (false == $html) {
                    echo $this->fieldRender($name, $data);
                } else {
                    echo $html;
                }
            },
            self::$slug,
            self::$slug . "_{$sectionPrefix}_section"
        );
    }

    private function fieldRender($name, $data)
    {
        $default = [
            'tag' => 'input',
            'name' => $name,
            'attrs' => [],
            'value' => ''
        ];

        if ('select' == $data['tag']) {
            $default['options'] = [];
            $default['selected'] = '';
        }

        $data = wp_parse_args($data, $default);

        $commonNamePrefix = self::$slug;

        $class = ' regular-text ';

        if (isset($data['attrs']['class'])) {
            $class .= $data['attrs']['class'];
            unset($data['attrs']['class']);
        }

        if (!isset($data['attrs']['type'])) {
            $data['attrs']['type'] = 'text';
        }

        $attributes = function () use ($data) {
            $out = '';
            if (empty($data['attrs'])) {
                return $out;
            }
            foreach ($data['attrs'] as $k => $v) {
                $out .= " {$k}='{$v}' ";
            }
            return $out;
        };

        $out = '';
        if ('input' == $data['tag']) {
            $out = "<input name='{$commonNamePrefix}[{$data['name']}]' value='{$data['value']}' class='{$class}' {$attributes()} >";
        } else if ('textarea' == $data['tag']) {
            $out = "<textarea name='{$commonNamePrefix}[{$data['name']}]' class='{$class}' {$attributes()} >{$data['value']}</textarea>";
        } else if ('select' == $data['tag']) {
            $out .= "<select name='{$commonNamePrefix}[{$data['name']}]' class='{$class}' {$attributes()} >";
            foreach ($data['options'] as $key => $value) {
                $out .= "<option value='{$key}' " . selected($data['selected'], $key, false) . " >{$value}</option>";
            }
            $out .= "</select>";
        }

        return $out;
    }

    private function section($prefix, $name = '', $html = '')
    {
        add_settings_section(
            self::$slug . "_{$prefix}_section",
            $name,
            function () use ($html) {
                echo $html;
            },
            self::$slug
        );

        return $prefix;
    }

    public function PageContent()
    {
        ?>
        <div class="wrap">
            <h2> <?php echo get_admin_page_title(); ?> </h2>
        </div>
        <form method="POST" action="options.php">
            <?php
            settings_fields(self::$slug);
            do_settings_sections(self::$slug);
            submit_button();
            ?>
        </form>
        <?php
    }


}
