<?php

namespace ImageProxy\Admin;


use ImageProxy\Utils\Assets;

class TestImageBlock
{

    use Assets;

    public function init()
    {

        add_action('ImageProxy__test-block', [$this, 'block']);
    }

    public function block()
    {
        $imgSrc = "{$this->url}public/images/kotik-ok.png"
        ?>

        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th scope="row">
                    <?php _e('Test image','ImageProxy');?>
                </th>
                <td>
                    <img alt="" src="<?php echo $imgSrc; ?>"/>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
    }
}
