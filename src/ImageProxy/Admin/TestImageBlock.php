<?php

namespace ImageProxy\Admin;


use ImageProxy\Utils\Assets;

class TestImageBlock
{

    use Assets;

    public function init()
    {
        $this->addAssets();
        add_action('ImageProxy__test-block', [$this, 'block']);
    }

    private function addAssets()
    {
        if (isset($_GET['page']) && $_GET['page'] == Page::$slug) {
            $this->addCss('TestImageBlock', 'admin');
        }
    }

    public function block()
    {
        $imgSrc = apply_filters('ImageProxy__convert-image-url', "{$this->url}public/images/kotik-ok.png");
        ?>

        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th scope="row">
                    <?php _e('Test image', 'ImageProxy'); ?>
                </th>
                <td>
                    <img class="ImageProxy__test-image" alt=" " width="50" height="50" src="<?php echo $imgSrc; ?>"/>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
    }
}
